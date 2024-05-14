<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VerifyFCMBTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:verifyfcmb-transaction';

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
        try {

            $this->info('***** VERIFYING TRANSACTION :: fOR FCMB COMMAND*************');

            $today = now()->toDateString();

            $checkTransaction = PaymentTransactions::whereDate('created_at', $today)
            ->whereIn('status', ['started', 'processing'])
            ->where("provider", "FCMB")
            ->chunk(10, function ($paymentLogs) use (&$paymentData) {

                foreach ($paymentLogs as $paymentLog) {
        
                    $FCMB_LINK = env("FCMB_TEST_URL");
                    $FCMB_MERCHANT_CODE = env("FCMB_TEST_MERCHANT_CODE");
                    $FCMB_AUTHORIZATION = env("FCMB_TEST_AUTHORIZATION");

                    $FULL_LINK = $FCMB_LINK."".$paymentLog->transaction_id;

                    $iresponse = Http::withHeaders([
                        'merchant_code' => $FCMB_MERCHANT_CODE, // flutterwave polaris
                        "Authorization" => $FCMB_AUTHORIZATION
                    ])->get($FULL_LINK);
        
                    $fcmbResponse = $iresponse->json(); 
        
                    \Log::info('FCMB Response COMMAND: ' . json_encode($fcmbResponse));

                    if($iresponse['code'] == 57) {
                        \Log::info('FCMB Response Code 57: ' . json_encode($fcmbResponse));
                        return $fcmbResponse;
                    };
            
                    
                     if (!isset($fcmbResponse['data']['transactionStatus']) && ($fcmbResponse['data']['transactionStatus'] != "Success")) {
                        \Log::info('FCMB data Error TransactionStatus not sucessful: COMMAND ' . json_encode($fcmbResponse));
                        return $fcmbResponse;
                    }

                    if ($fcmbResponse['data']['transactionStatus'] == "Success") {
                        $update = PaymentTransactions::where("transaction_id", $this->checkTrans->transaction_id)->update([
                            'providerRef' => $fcmbResponse['data']['transactionRef'],
                            'Descript' => $fcmbResponse['data']['transactionStatus'],
                            'response_status' => 1,
                            'provider' => $this->request->provider,
                        ]);
            
                        \Log::info('Successful: ' . json_encode($fcmbResponse));
                      return $fcmbResponse;
                    } else {
                        \Log::info('FCMB Unkown Error: ' . json_encode($fcmbResponse));
                        return $fcmbResponse;
                        //return $this->sendError($fcmbResponse, "Error Verifying Payment", Response::HTTP_BAD_REQUEST);
                    }

                }

            });
    
    

        }catch(\Exception $e){
            $this->info('***** ERROR PROCESSING PAYMENT :: Error Processing and updating payments *************');
            Log::error('Error in Payment LookUp: ' . $e->getMessage());
        }


    }
}
