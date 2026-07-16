<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\PrePaidPaymentMail;
use App\Models\Transactions\PaymentTransactions;
use App\Models\ECMI\EcmiPayments;

class PrepaidJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payment;

    /**
     * Number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * Time (seconds) before retrying the job after a failure.
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $baseUrl = env('MIDDLEWARE_URL');
        $addCustomerUrl = $baseUrl . 'vendelect';

        $data = [
            'meterno' => $this->payment['meterNo'],
            'vendtype' => $this->payment['account_type'],
            'amount' => $this->payment['amount'],
            'provider' => $this->payment['disco_name'],
            'custname' => $this->payment['customerName'],
            'businesshub' => $this->payment['BUID'],
            'custphoneno' => $this->payment['phone'],
            'payreference' => $this->payment['transaction_id'],
            'colagentid' => 'IB001',
            'email' => $this->payment['email'],
        ];

        $transaction = PaymentTransactions::where('transaction_id', $this->payment['transaction_id'])->first();

        if (!$transaction) {
            Log::error('PrepaidJob: Transaction not found', [
                'transaction_id' => $this->payment['transaction_id']
            ]);
            return;
        }

        // ✅ Only process if transaction.status == 'processing'
        if ($transaction->status !== 'processing') {
            Log::info('PrepaidJob: Skipping transaction (status not processing)', [
                'transaction_id' => $transaction->transaction_id,
                'status' => $transaction->status,
            ]);
            return;
        }

        // ✅ Ensure providerRef exists
        if (empty($transaction->providerRef)) {
            Log::warning('PrepaidJob: providerRef missing — cannot generate token', [
                'transaction_id' => $transaction->transaction_id,
            ]);
            return;
        }

        try {
            // Send request to middleware
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->withHeaders(['Authorization' => env('MIDDLEWARE_TOKEN')])
                ->post($addCustomerUrl, $data);

            if (!$response->successful()) {
                Log::error('PrepaidJob: Middleware HTTP error', [
                    'transaction_id' => $transaction->transaction_id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return;
            }

            $newResponse = $response->json();
            Log::info('PrepaidJob: Middleware response', ['response' => $newResponse]);

            // ✅ Process only successful responses
            if (isset($newResponse['status']) && $newResponse['status'] === 'true') {

                $receipt = $newResponse['recieptNumber'] ?? ($newResponse['data']['recieptNumber'] ?? null);

                PaymentTransactions::where('transaction_id', $transaction->transaction_id)->update([
                    'status' => 'success',
                    'receiptno' => $receipt,
                    'Descript' => $newResponse['message'] ?? '',
                    'units' => $newResponse['Units'] ?? ($newResponse['data']['Units'] ?? ''),
                    'minimumPurchase' => $newResponse['customer']['minimumPurchase'] ?? '',
                    'tariffcode' => $newResponse['customer']['tariffcode'] ?? '',
                    'customerArrears' => $newResponse['customer']['customerArrears'] ?? '',
                    'tariff' => $newResponse['customer']['tariff'] ?? '',
                    'serviceBand' => $newResponse['customer']['serviceBand'] ?? '',
                    'feederName' => $newResponse['customer']['feederName'] ?? '',
                    'dssName' => $newResponse['customer']['dssName'] ?? '',
                    'udertaking' => $newResponse['customer']['undertaking'] ?? '',
                    'VAT' => isset($newResponse['transactionReference'])
                        ? EcmiPayments::where('transref', $newResponse['transactionReference'])->value('VAT')
                        : 0,
                    'costOfUnits' => isset($newResponse['transactionReference'])
                        ? EcmiPayments::where('transref', $newResponse['transactionReference'])->value('CostOfUnits')
                        : 0,
                ]);

                Log::info('PrepaidJob: Transaction successfully updated', [
                    'transaction_id' => $transaction->transaction_id,
                    'receipt' => $receipt,
                ]);

                // Send notifications
               // $this->sendSms($receipt);
                $this->sendEmail($receipt);

            } else {
                Log::warning('PrepaidJob: Middleware returned non-success response', [
                    'transaction_id' => $transaction->transaction_id,
                    'response' => $newResponse,
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('PrepaidJob: Exception occurred', [
                'transaction_id' => $this->payment['transaction_id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->fail($e);
        }
    }

    /**
     * Send SMS notification to customer.
     */
    private function sendSms($token): void
    {
        try {
            $smsData = [
                'token' => "p42OVwe8CF2Sg6VfhXAi8aBblMnADKkuOPe65M41v7jMzrEynGQoVLoZdmGqBQIGFPbH10cvthTGu0LK1duSem45OtA076fLGRqX",
                'sender' => 'IBEDC',
                'to' => $this->payment['phone'],
                'message' => "Meter Token: $token. Payment of {$this->payment['amount']} for Meter No {$this->payment['meterNo']} successful. REF: {$this->payment['transaction_id']}. For Support: 07001239999",
                'type' => 0,
                'routing' => 3,
            ];

            $smsResponse = Http::asForm()->post(env('SMS_MESSAGE'), $smsData);

            Log::info('PrepaidJob: SMS sent successfully', [
                'transaction_id' => $this->payment['transaction_id'],
                'response' => $smsResponse->body(),
            ]);

        } catch (\Throwable $e) {
            Log::error('PrepaidJob: SMS sending failed', [
                'transaction_id' => $this->payment['transaction_id'],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send email notification to customer and agent.
     */
    private function sendEmail($token): void
    {
        try {
            $emailData = [
                'token' => $token,
                'meterno' => $this->payment['meterNo'],
                'amount' => $this->payment['amount'],
                'custname' => $this->payment['customerName'],
                'custphoneno' => $this->payment['phone'],
                'payreference' => $this->payment['transaction_id'],
            ];

            $user = Auth::user();

            if (!empty($user?->email) && $user->email !== "null") {
                Mail::to($user->email)->send(new PrePaidPaymentMail($emailData));
            }

            if (!empty($this->payment['email'])) {
                Mail::to($this->payment['email'])->send(new PrePaidPaymentMail($emailData));
            }

            Log::info('PrepaidJob: Email(s) sent', [
                'transaction_id' => $this->payment['transaction_id'],
                'user_email' => $user->email ?? null,
                'customer_email' => $this->payment['email'],
            ]);

        } catch (\Throwable $e) {
            Log::error('PrepaidJob: Email sending failed', [
                'transaction_id' => $this->payment['transaction_id'],
                'error' => $e->getMessage(),
            ]);
        }
    }
}
