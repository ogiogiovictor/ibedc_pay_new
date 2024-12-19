<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transactions\PaymentTransactions;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Mail;
use App\Mail\PrePaidPaymentMail;
use App\Models\ECMI\EcmiPayments;
use Illuminate\Support\Facades\Auth;

class PrepaidLookUpImproved extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prepaid-look-up-improved';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('***** STARTING PREPAID PROCESSING: Starting to push Pending Prepaid Payments *************');
        $paymentData = [];
        DB::connection()->enableQueryLog();

        try {
            $hasMorePayments = true;
            $chunkSize = 30;
            $page = 1;

            do {
                $prepaidTransaction = PaymentTransactions::whereNull('receiptno')
                    ->where('account_type', 'Prepaid')
                    ->where('status', 'processing')
                    ->whereNotNull('providerRef')
                    ->orderby('created_at', 'desc')
                    ->skip(($page - 1) * $chunkSize)
                    ->take($chunkSize)
                    ->get();

                if ($prepaidTransaction->isEmpty()) {
                    $hasMorePayments = false;
                    continue;
                }

                foreach ($prepaidTransaction as $paymentLog) {
                    if (!is_numeric($paymentLog->amount) || $paymentLog->amount < 0) {
                        Log::error("Invalid amount for transaction ID {$paymentLog->transaction_id}: {$paymentLog->amount}");
                        continue;
                    }

                    $baseUrl = env('MIDDLEWARE_URL');
                    $addCustomerUrl = $baseUrl . 'vendelect';

                    $data = [
                        'meterno' => $paymentLog->meter_no,
                        'vendtype' => $paymentLog->account_type,
                        'amount' => $paymentLog->amount,
                        'provider' => "IBEDC",
                        "custname" => $paymentLog->customer_name,
                        "businesshub" => $paymentLog->BUID,
                        "custphoneno" => $paymentLog->phone,
                        "payreference" => $paymentLog->transaction_id,
                        "colagentid" => "IB001",
                    ];

                    $response = Http::withoutVerifying()->withHeaders([
                        'Authorization' => env('MIDDLEWARE_TOKEN'),
                    ])->post($addCustomerUrl, $data);

                    $newResponse = $response->json();
                    \Log::info('RESPONSE FROM MOMAS API - PREPAID LOG: ' . json_encode($newResponse));

                    if ($newResponse['status'] == "true") {
                        $this->processSuccessfulTransaction($paymentLog, $newResponse);
                        $this->sendNotification($paymentLog, $newResponse);
                    }
                }

                $page++;
                
            } while ($hasMorePayments);

            \Log::info(DB::getQueryLog());
            $this->info('***** PAYMENT COMPLETED:: All payments processed successfully *************');

        } catch (\Exception $e) {
            \Log::info('ERROR MESSAGE - PREPAID LOG: ' . json_encode($e));
            \Log::info(DB::getQueryLog());
            $this->info('***** TOKENLOOKUP API PAYMENT COMPLETED:: All payments processed successfully *************');
        }
    }


    protected function processSuccessfulTransaction($paymentLog, $newResponse)
    {
        PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
            'status' => 'success',
            'receiptno' => $newResponse['recieptNumber'] ?? $newResponse['data']['recieptNumber'],
            'Descript' => ($newResponse['message'] ?? $newResponse['transaction_status']) . "-" . $newResponse['transactionReference'],
            'units' => $newResponse['Units'] ?? $newResponse['data']['Units'],
            'minimumPurchase' => $newResponse['customer']['minimumPurchase'] ?? '',
            'tariffcode' => $newResponse['customer']['tariffcode'] ?? '',
            'customerArrears' => $newResponse['customer']['customerArrears'] ?? '',
            'tariff' => $newResponse['customer']['tariff'] ?? '',
            'serviceBand' => $newResponse['customer']['serviceBand'] ?? '',
            'feederName' => $newResponse['customer']['feederName'] ?? '',
            'dssName' => $newResponse['customer']['dssName'] ?? '',
            'udertaking' => $newResponse['customer']['undertaking'] ?? '',
            'VAT' => EcmiPayments::where("transref", $newResponse['transactionReference'])->value('VAT'),
            'costOfUnits' => EcmiPayments::where("transref", $newResponse['transactionReference'])->value('CostOfUnits'),
        ]);
    }

    protected function sendNotification($paymentLog, $newResponse)
    {
        $token = $newResponse['recieptNumber'] ?? $newResponse['data']['recieptNumber'];
        $smsData = [
            'token' => env('SMS_TOKEN'),
            'sender' => "IBEDC",
            'to' => $paymentLog->phone,
            "message" => "Meter Token: $token  Your IBEDC Prepaid payment of $paymentLog->amount for Meter No $paymentLog->meter_no  was successful. REF: $paymentLog->transaction_id. For Support: 07001239999",
            "type" => 0,
            "routing" => 3,
        ];

        $smsResponse = Http::asForm()->post(env('SMS_MESSAGE'), $smsData);
        \Log::info("SMS SENT SUCCESSFULLY: " . json_encode($smsData));

        $emailData = [
            'token' => $token,
            'meterno' => $paymentLog->meter_no,
            'amount' => $paymentLog->amount,
            "custname" => $paymentLog->customer_name,
            "custphoneno" => $paymentLog->phone,
            "payreference" => $paymentLog->transaction_id,
        ];

        $user = Auth::user();

        if (!str_starts_with($user->email, 'default') && $paymentLog->email && !str_starts_with($paymentLog->email, 'default')) {
            Mail::to($user->email)->cc($paymentLog->email)->send(new PrePaidPaymentMail($emailData));
        }

        $this->info('***** SMS SUCCESSFULLY SENT :: SMS has been sent to the customer *************');
    }
}
