<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transactions\PaymentTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PrePaidPaymentMail;
use App\Models\ECMI\EcmiPayments;
use Illuminate\Support\Facades\Auth;

class PrepaidLookUpImprove extends Command
{
    protected $signature = 'app:prepaid-look-up-improve';
    protected $description = 'Lookup token and send it to the customer for pending prepaid payments';

    public function handle()
    {
        $this->info('>>> STARTING PREPAID PROCESSING: Looking up pending prepaid payments...');

        try {
            PaymentTransactions::whereNull('receiptno')
                ->where('account_type', 'Prepaid')
                ->where('status', 'processing')
                ->whereNotNull('providerRef')
                ->orderBy('created_at', 'desc')
                ->chunk(10, function ($transactions) {
                    foreach ($transactions as $transaction) {
                        $this->processTransaction($transaction);
                    }
                });

            $this->info('>>> ALL PAYMENTS PROCESSED SUCCESSFULLY');
        } catch (\Throwable $e) {
            Log::error('PREPAID LOOKUP ERROR: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->error('>>> ERROR OCCURRED. Check logs.');
        } finally {
            // ✅ Explicitly close the DB connection
            DB::disconnect();
        }
    }

    private function processTransaction($transaction)
    {
        if (!is_numeric($transaction->amount) || $transaction->amount < 0) {
            Log::warning("Invalid amount for transaction {$transaction->transaction_id}: {$transaction->amount}");
            return;
        }

        $response = $this->sendVendRequest($transaction);
        if (!$response || ($response['status'] ?? '') !== 'true') {
            Log::error("Vend failed for {$transaction->transaction_id}", ['response' => $response]);
            return;
        }

        $this->updateTransaction($transaction, $response);
        $this->sendSms($transaction, $response);
        $this->sendEmail($transaction, $response);
    }

    private function sendVendRequest($transaction)
    {
        $url = rtrim(env('MIDDLEWARE_URL'), '/').'/vendelect';

        $payload = [
            'meterno'     => $transaction->meter_no,
            'vendtype'    => $transaction->account_type,
            'amount'      => $transaction->amount,
            'provider'    => 'IBEDC',
            'custname'    => $transaction->customer_name,
            'businesshub' => $transaction->BUID,
            'custphoneno' => $transaction->phone,
            'payreference'=> $transaction->transaction_id,
            'colagentid'  => 'IB001',
        ];

        $response = Http::withoutVerifying()
            ->withHeaders(['Authorization' => env('MIDDLEWARE_TOKEN')])
            ->post($url, $payload);

        $json = $response->json();
        Log::info("MOMAS API RESPONSE", $json);

        return $json;
    }

    private function updateTransaction($transaction, $response)
    {
        $ref = $response['transactionReference'] ?? '';
        $receipt = $response['recieptNumber'] ?? $response['data']['recieptNumber'] ?? '';
        $units = $response['Units'] ?? $response['data']['Units'] ?? '';

        PaymentTransactions::where('transaction_id', $transaction->transaction_id)->update([
            'status'          => 'success',
            'receiptno'       => $receipt,
            'Descript'        => ($response['message'] ?? $response['transaction_status'] ?? '').'-'.$ref,
            'units'           => $units,
            'minimumPurchase' => $response['customer']['minimumPurchase'] ?? '',
            'tariffcode'      => $response['customer']['tariffcode'] ?? '',
            'customerArrears' => $response['customer']['customerArrears'] ?? '',
            'tariff'          => $response['customer']['tariff'] ?? '',
            'serviceBand'     => $response['customer']['serviceBand'] ?? '',
            'feederName'      => $response['customer']['feederName'] ?? '',
            'dssName'         => $response['customer']['dssName'] ?? '',
            'udertaking'      => $response['customer']['undertaking'] ?? '',
            'VAT'             => EcmiPayments::where('transref', $ref)->value('VAT'),
            'costOfUnits'     => EcmiPayments::where('transref', $ref)->value('CostOfUnits'),
        ]);

        Log::info("Transaction updated successfully: {$transaction->transaction_id}");
    }

    private function sendSms($transaction, $response)
    {
        $token = $response['recieptNumber'] ?? $response['data']['recieptNumber'] ?? '';
        $url = env('SMS_MESSAGE');

        $smsPayload = [
            'token'   => env('SMS_TOKEN'),
            'sender'  => 'IBEDC',
            'to'      => $transaction->phone,
            'message' => "Meter Token: $token. Your IBEDC Prepaid payment of {$transaction->amount} for Meter No {$transaction->meter_no} was successful. REF: {$transaction->transaction_id}. For Support: 07001239999",
            'type'    => 0,
            'routing' => 3,
        ];

        Http::asForm()->post($url, $smsPayload);
        Log::info("SMS sent for transaction: {$transaction->transaction_id}");
    }

    private function sendEmail($transaction, $response)
    {
        $user = Auth::user();
        $token = $response['recieptNumber'] ?? $response['data']['recieptNumber'] ?? '';

        $mailData = [
            'token'     => $token,
            'meterno'   => $transaction->meter_no,
            'amount'    => $transaction->amount,
            'custname'  => $transaction->customer_name,
            'custphoneno'=> $transaction->phone,
            'payreference'=> $transaction->transaction_id,
        ];

        $recipients = [];
        if ($user && !str_starts_with($user->email, 'default')) {
            $recipients[] = $user->email;
        }
        if ($transaction->email && !str_starts_with($transaction->email, 'default')) {
            $recipients[] = $transaction->email;
        }

        if (!empty($recipients)) {
            Mail::to(array_shift($recipients))->cc($recipients)->send(new PrePaidPaymentMail($mailData));
            Log::info("Email sent for transaction: {$transaction->transaction_id}");
        }
    }
}
