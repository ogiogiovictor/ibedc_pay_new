<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transactions\PaymentTransactions;
use Symfony\Component\HttpFoundation\Response;

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

            $this->info('***** FLUTTERWAVE VERIFY DAILY PAYMENT API :: Lookup Started *************');

            $today = now()->toDateString();

            $checkTransaction = PaymentTransactions::whereDate('created_at', $today)
            ->whereIn('status', ['started', 'processing'])
            ->chunk(10, function ($paymentLogs) use (&$paymentData) {

                foreach ($paymentLogs as $paymentLog) {
        
                    $flutterData = [
                        'SECKEY' =>  env("FLUTTER_POLARIS_KEY"), // 'FLWSECK-d1c7523a58aad65d4585d47df227ee25-X',
                        "txref" => $paymentLog->transaction_id
                    ];

                    $flutterUrl = env("FLUTTER_WAVE_URL");

                    $iresponse = Http::post($flutterUrl, $flutterData);
                    $flutterResponse = $iresponse->json(); 

                    if ($flutterResponse['status'] == "success" && $flutterResponse['data']['status'] == 'successful') {
                        $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                            'providerRef' => $flutterResponse['data']['flwref'],
                        ]);
    
                        \Log::info("payment Reference Updated Successfully ". json_encode($flutterResponse));
                    }
                }

            });
    
    


        }catch(\Exception $e){
            $this->info('***** ERROR PROCESSING PAYMENT :: Error Processing and updating payments *************');
            Log::error('Error in Payment LookUp: ' . $e->getMessage());
        }
    }
}
