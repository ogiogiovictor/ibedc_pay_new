<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transactions\PaymentTransactions;
use Symfony\Component\HttpFoundation\Response;

class FailedTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:failed-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'All Failed Transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            $this->info('***** FLUTTERWAVE FAILED RESPONSES PAYMENT API :: Lookup Failed *************');

            $today = now()->toDateString();

            PaymentTransactions::whereDate('created_at', $today)->where('status', 'failed')
            ->chunk(10, function ($paymentLogs) use (&$paymentData) {

                foreach ($paymentLogs as $paymentLog) {
        
                    $flutterData = [
                        'SECKEY' =>  env("FLUTTER_POLARIS_KEY"), // 'FLWSECK-d1c7523a58aad65d4585d47df227ee25-X',
                        "txref" => $paymentLog->transaction_id
                    ];

                    $flutterUrl = env("FLUTTER_WAVE_URL");

                    $iresponse = Http::post($flutterUrl, $flutterData);
                    $flutterResponse = $iresponse->json(); 

                    $this->info('***** AWAITING RESPONSE FROM FLUTTERWAVE :: Lookup Failed 2 *************');

                    if ($flutterResponse['status'] == "success" && $flutterResponse['data']['status'] == 'successful') {

                        $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                           // 'providerRef' => $flutterResponse['data']['flwref'],
                            'status' => 'processing'
                        ]);

                        $this->info('***** ONE TRANSACTION UPDATED ::A TRANSACTION HAS BEEN UPDATED *************');

                    } else { 
                        // $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                        //     'providerRef' => $flutterResponse['data']['flwref'],
                        //     'status' => 'failed'
                        // ]);
                        $this->info('***** ONE TRANSACTION FAILED ::A TRANSACTION IS FAILING *************');
                        // Send Failed Response to Customer
                    }
                }

            });
    
    


        }catch(\Exception $e){
            $this->info('***** ERROR PROCESSING PAYMENT :: Error Processing and updating payments *************');
            Log::error('Error Failed LookUp: ' . $e->getMessage());
        }
    }
}
