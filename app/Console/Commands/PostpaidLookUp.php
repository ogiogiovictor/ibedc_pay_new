<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transactions\PaymentTransactions;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Jobs\PostPaidJob;
use Carbon\Carbon;
//use App\Services\PolarisLogService;

class PostpaidLookUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:postpaid-look-up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Postpaid Payment for customers using postpaid';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try{

            $this->info('***** POSTPAID API :: Lookup Started *************');

            $checkTransaction = PaymentTransactions::whereNull('receiptno')
            ->where('account_type', 'Postpaid')
            ->where('status', 'processing')
            //->orWhere('status', 'pending')
            ->whereNotNull('providerRef')
            ->chunk(10, function ($paymentLogs) use (&$paymentData) {

                foreach ($paymentLogs as $paymentLog) {

                    $baseUrl = env('MIDDLEWARE_URL');  //MIDDLEWARE_URL
                    $addCustomerUrl = $baseUrl . 'vendelect';
            
                    $data = [
                        'meterno' => $paymentLog->account_number,
                        'vendtype' => $paymentLog->account_type,
                        'amount' => $paymentLog->amount, 
                        "provider" => "IBEDC",
                        "custname" => $paymentLog->customer_name,
                        "businesshub" => $paymentLog->BUID,
                        "custphoneno" => $paymentLog->phone,
                        "payreference" => $paymentLog->transaction_id,
                        "colagentid" => "IB001",
                                         
                    ];
            
                    $response = Http::withoutVerifying()->withHeaders([
                        'Authorization' => env('MIDDLEWARE_TOKEN'),   //MIDDLEWARE_TOKEN
                    ])->post($addCustomerUrl, $data);
            
                    $newResponse =  $response->json();

                    $this->info('***** POSTPAID API :: Processing Postpaid Payment*************');
                     \Log::info('Postpaid Data Log response: ' . json_encode($newResponse));
                      \Log::info('Postpaid Response Status: ' . $newResponse['status'] );


                    if($newResponse['status'] == "true") { 
                        //Update the status of payment and send the job and send SMS
                        $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                            'response_status' => 1,
                            'status' =>  'success',
                            'receiptno' =>   isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] :  $newResponse['data']['recieptNumber'], //Carbon::now()->format('YmdHis'),

                            'Descript' =>  isset($newResponse['message']) ? $newResponse['message'] :  '',
                            'units' => isset($newResponse['Units']) ? $newResponse['Units'] : '0', 

                            'minimumPurchase' => isset($newResponse['customer']['minimumPurchase']) ? $newResponse['customer']['minimumPurchase'] : '',
                            'tariffcode'  => isset($newResponse['customer']['tariffcode']) ? $newResponse['customer']['tariffcode'] : '',
                            'customerArrears' => isset($newResponse['customer']['customerArrears']) ? $newResponse['customer']['customerArrears'] : '',
                        
                            'udertaking' => isset($newResponse['customer']['businessUnitId']) ? $newResponse['customer']['businessUnitId'] : '',

                        ]);
                        dispatch(new PostPaidJob($paymentLog));
                        \Log::info('Postpaid Payment Successfuly: ' . json_encode($newResponse));  //transactionStatus
                    } else if($newResponse['status'] == "false" && $newResponse['transactionStatus']  == "Success" ){

                        $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                            'response_status' => 1,
                            'status' =>  'success',
                          //  'receiptno' =>   isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] :  $newResponse['data']['recieptNumber'], //Carbon::now()->format('YmdHis'),

                            // 'Descript' =>  isset($newResponse['message']) ? $newResponse['message'] :  '',
                            // 'units' => isset($newResponse['Units']) ? $newResponse['Units'] : '0', 

                            // 'minimumPurchase' => isset($newResponse['customer']['minimumPurchase']) ? $newResponse['customer']['minimumPurchase'] : '',
                            // 'tariffcode'  => isset($newResponse['customer']['tariffcode']) ? $newResponse['customer']['tariffcode'] : '',
                            // 'customerArrears' => isset($newResponse['customer']['customerArrears']) ? $newResponse['customer']['customerArrears'] : '',
                        
                            // 'udertaking' => isset($newResponse['customer']['businessUnitId']) ? $newResponse['customer']['businessUnitId'] : '',

                        ]);
                        dispatch(new PostPaidJob($paymentLog));
                        \Log::info('Transaction Data: ' .  $newResponse['transactionStatus']);
                        \Log::info('Postpaid Payment Successfuly: ' . json_encode($newResponse)); 
                    }

                }

            });

        }catch(\Exception $e){
            $this->info('***** POSTPAID API :: Error Processing Postpaid Payment *************');
            \Log::error("Error Processing Payment: ". $e->getMessage());
        }
    }
}
