<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transactions\PaymentTransactions;
use Symfony\Component\HttpFoundation\Response;
use App\Services\PolarisLogService;

class FCMBFlutterVerifyTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fcmbflutter-verify-transactions';

    /**
     * The console command description.
     *


     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            $this->info('***** FLUTTERWAVE VERIFY DAILY PAYMENT API :: Lookup Initiated --- *************');

            $today = now()->toDateString();

            //$checkTransaction = PaymentTransactions::whereIn('status', ['started', 'processing'])
            $checkTransaction = PaymentTransactions::whereDate('created_at',  $today)  //'2024-09-20'   $today
            ->where("provider", "FCMB")
            ->whereIn('status', ['started', 'processing'])
            ->chunk(5, function ($paymentLogs) use (&$paymentData) {

                
                foreach ($paymentLogs as $paymentLog) {

                    $flutterData = [
                        'SECKEY' =>   env('FLUTTER_FCMB_KEY'), // 'FLWSECK-d1c7523a58aad65d4585d47df227ee25-X',
                        "txref" => $paymentLog->transaction_id
                    ];

                    $flutterUrl = env("FLUTTER_WAVE_URL");

                    $iresponse = Http::post($flutterUrl, $flutterData);
                    $flutterResponse = $iresponse->json(); 

                    $this->info('***** FLUTTERWAVE Processing Payments *************');

                    \Log::info("BEFORE PAYMENT LOOKUP ". json_encode($flutterResponse));

                    if (isset($flutterResponse['status']) && $flutterResponse['status'] == "success" && isset($flutterResponse['data']['status']) && $flutterResponse['data']['status'] == 'successful') {

                        if ($paymentLog->status == "processing") {
                            $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                                'providerRef' => $flutterResponse['data']['flwref'],
                                'provider' => 'FCMB'
                            ]);
                            $this->info('***** FLUTTERWAVE Verification Was was update with provider and reference *************');

                        } else if ($paymentLog->status == "started") {
                            $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                                'providerRef' => $flutterResponse['data']['flwref'],
                                'provider' => 'FCMB',
                                'status' => 'processing'
                            ]);
                            $this->info('***** FLUTTERWAVE Transaction is set to processing *************');
                        } else if ($paymentLog->status == "cancelled") {
                            $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                                'providerRef' => $flutterResponse['data']['flwref'],
                                'provider' => 'FCMB',
                                'status' => 'processing'
                            ]);
                            $this->info('***** FLUTTERWAVE Transaction is set to processing *************');
                        } else {
                            $this->info('***** FLUTTERWAVE Verification failed or unknown *************');
                            \Log::info("We don't know the status " . json_encode($flutterResponse));
                        }
    
                       
                    (new PolarisLogService)->processLogs($paymentLog->transaction_id, $paymentLog->meter_no,  $paymentLog->account_number, $flutterResponse);
    
                    } elseif (isset($flutterResponse['status']) && isset($flutterResponse['data']['status']) && $flutterResponse['data']['status'] == 'failed') {
    
                        $this->info('***** FLUTTERWAVE TRANSACTION FAILED :- FAILED STATUS *************');

                        $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                            'providerRef' => $flutterResponse['data']['flwref'],
                            'status' => 'failed',
                            'provider' => 'FCMB'
                        ]);
                        // Send Failed Response to Customer
                        (new PolarisLogService)->processLogs($paymentLog->transaction_id, $paymentLog->meter_no,  $paymentLog->account_number, $flutterResponse);

                    }  elseif (isset($flutterResponse['status']) && isset($flutterResponse['data']['status']) && $flutterResponse['data']['status'] == 'cancelled') {
    
                        $this->info('***** FLUTTERWAVE TRANSACTION FAILED :- FAILED STATUS *************');

                        $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                            'providerRef' => $flutterResponse['data']['flwref'],
                            'status' => 'cancelled',
                            'provider' => 'FCMB'
                        ]);
                        // Send Failed Response to Customer
                        (new PolarisLogService)->processLogs($paymentLog->transaction_id, $paymentLog->meter_no,  $paymentLog->account_number, $flutterResponse);

                    } else {

                        // $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                        //     'status' => 'cancelled',
                        //     'provider' => 'FCMB'
                        // ]);

                        (new PolarisLogService)->processLogs($paymentLog->transaction_id, $paymentLog->meter_no,  $paymentLog->account_number, $flutterResponse);
                    }
    
                   
                    \Log::error("Payment Response" . json_encode($flutterResponse));


                   
                }

            });
    
    


        }catch(\Exception $e){
            $this->info('***** ERROR PROCESSING PAYMENT :: Error Processing and updating payments *************');
            Log::error('Error in Payment LookUp: ' . $e->getMessage());
            //Log::error('Error Response: ' . json_encode($flutterResponse));
        }
    }
}
