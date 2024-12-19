<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transactions\PaymentTransactions;
use Symfony\Component\HttpFoundation\Response;
use App\Services\PolarisLogService;

class WeeklyPaymentCheckup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:weekly-payment-checkup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Weekly Payment Setup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            $this->info('***** FLUTTERWAVE VERIFY DAILY PAYMENT API :: Lookup Initiated --- *************');

            $today = now()->toDateString();

             // Get the start and end of the current week (Monday to Sunday)
            $startOfWeek = now()->startOfWeek(); // Monday
            $endOfWeek = now()->endOfWeek();     // Sunday

            //$checkTransaction = PaymentTransactions::whereIn('status', ['started', 'processing'])
            $checkTransaction = PaymentTransactions::whereDate('created_at',   [$startOfWeek, $endOfWeek])  //'2024-09-20'   $today
            ->whereIn('status', ['started', 'processing'])
            ->inRandomOrder() // Select transactions in random order
            ->chunk(5, function ($paymentLogs) use (&$paymentData) {

                
                foreach ($paymentLogs as $paymentLog) {

                   // $providerKey = $paymentLog->provider === 'Polaris' ? env("FLUTTER_POLARIS_KEY") : env('FLUTTER_FCMB_KEY');
                    $providerKey = in_array($paymentLog->provider, ['Polaris', null]) ? env("FLUTTER_POLARIS_KEY") : env('FLUTTER_FCMB_KEY');


        
                    $flutterData = [
                        'SECKEY' =>  $providerKey, // env("FLUTTER_POLARIS_KEY"), // 'FLWSECK-d1c7523a58aad65d4585d47df227ee25-X',
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
                            'status' => 'failed'
                        ]);
                        // Send Failed Response to Customer
                        (new PolarisLogService)->processLogs($paymentLog->transaction_id, $paymentLog->meter_no,  $paymentLog->account_number, $flutterResponse);

                    } else {

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
