<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transactions\PaymentTransactions;
use Symfony\Component\HttpFoundation\Response;
use App\Services\PolarisLogService;

class PaymentLookUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:payment-look-up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Payment Jobs';

    /**
     * Execute the console command.
     */
    
    public function handle()
    {
        try {

            $this->info('***** FLUTTERWAVE VERIFY DAILY PAYMENT API :: Lookup Initiated --- *************');

            $today = now()->toDateString();

            //$checkTransaction = PaymentTransactions::whereIn('status', ['started', 'processing'])  // ->whereIn('status', ['started', 'processing'])
            $checkTransaction = PaymentTransactions::whereDate('created_at',  $today)  //'2024-09-20'   $today
            ->whereIn('status', ['started'])
            ->where('response_status', '!=', '3')
            ->chunk(5, function ($paymentLogs) use (&$paymentData) {

                
                foreach ($paymentLogs as $paymentLog) {

                  //  $providerKey = $paymentLog->provider === 'Polaris' ? env("FLUTTER_POLARIS_KEY") : env('FLUTTER_FCMB_KEY');
                   
                   $providerKey = match ($paymentLog->provider) {
                        'FCMB' => env('FLUTTER_FCMB_KEY'),
                        'Polaris' => env('FLUTTER_POLARIS_KEY'),
                        default => env('FLUTTER_POLARIS_KEY'), // Use a default key if provider is not specified
                      };
        
                    $flutterData = [
                        'SECKEY' =>  env("FLUTTER_POLARIS_KEY"), // 'FLWSECK-d1c7523a58aad65d4585d47df227ee25-X', $providerKey, //
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
                            ]);
                            $this->info('***** FLUTTERWAVE Verification Was Successful *************');

                        } else if ($paymentLog->status == "started") {
                            $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                                'providerRef' => $flutterResponse['data']['flwref'],
                                'status' => 'processing',
                                 'response_status' => 3
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
                            'response_status' => 3
                        ]);
                        // Send Failed Response to Customer
                        (new PolarisLogService)->processLogs($paymentLog->transaction_id, $paymentLog->meter_no,  $paymentLog->account_number, $flutterResponse);

                    } elseif (isset($flutterResponse['status']) && isset($flutterResponse['data']['status']) && $flutterResponse['data']['status'] == 'cancelled') {
    
                        $this->info('***** FLUTTERWAVE TRANSACTION FAILED :- FAILED STATUS *************');

                        $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                            'response_status' => 3
                        ]);
                        // Send Failed Response to Customer
                        (new PolarisLogService)->processLogs($paymentLog->transaction_id, $paymentLog->meter_no,  $paymentLog->account_number, $flutterResponse);

                    } else {
                        

                        $this->info('***** FLUTTERWAVE TRANSACTION NOT APPLICABLE *************');
                        $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                            'response_status' => 3
                        ]);
                        // $this->info('***** FLUTTERWAVE TRANSACTION CANCELLED :- CANCELLED STATUS *************');

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
