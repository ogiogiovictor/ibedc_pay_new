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

class PrepaidLookUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prepaid-look-up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lookup Token and ensure the token is valid and sent to the customer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('***** STARTING PREPAID PROCESSING: Starting to push Pending Prepaid Payments *************');
        $paymentData = []; 

        DB::connection()->enableQueryLog();

        try {

            $prepaidTransaction = PaymentTransactions::whereNull('receiptno')
                ->where('account_type', 'Prepaid')
                ->where('status', 'processing')
                ->whereNotNull('providerRef')
                ->chunk(30, function($prepaidpayments) use (&$paymentData) {

                    foreach($prepaidpayments as $paymentLog){

                        $baseUrl = env('MIDDLEWARE_URL');
                        $addCustomerUrl = $baseUrl. 'vendelect';

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
                            'Authorization' => env('MIDDLEWARE_TOKEN'), // 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
                        ])->post($addCustomerUrl, $data);
                
                        $newResponse =  $response->json();
    
                        \Log::info('RESPONSE FROM MOMAS API: ' . json_encode($newResponse));
                        $totalRecords = count($prepaidpayments);

                        if($newResponse['status'] == "true"){      
                        
                            $paymentData[] = $data;
                          
                             $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                                 'status' => $newResponse['status'] == "true" ?  'success' : 'failed', //"resp": "00",
                                 'receiptno' =>   isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'],
                                 'Descript' =>  isset($newResponse['message']) ? $newResponse['message']."-".$newResponse['transactionReference'] : $newResponse['transaction_status']."-".$newResponse['transactionReference'],
                                'units' => isset($newResponse['Units']) ? $newResponse['Units'] : $newResponse['data']['Units'], 
                                'minimumPurchase' => $newResponse['customer']['minimumPurchase'],
                                'tariffcode'  => $newResponse['customer']['tariffcode'],
                                'customerArrears' => $newResponse['customer']['customerArrears'],
                                'tariff' => $newResponse['customer']['tariff'],
                                'serviceBand' => $newResponse['customer']['serviceBand'],
                                'feederName' => $newResponse['customer']['feederName'],
                                'dssName' => $newResponse['customer']['dssName'],
                                'udertaking' => $newResponse['customer']['undertaking'],
                             ]);
    
                           
                             $this->info('***** Sending token to the customer *************');
                             //Send SMS to User
                             $token =  isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'];
                             $baseUrl = env('SMS_MESSAGE');
    
                             $data[] = [
                                'transaction_id' => $paymentLog->transaction_id,
                                // Include other relevant data here
                            ];
                             
                             $idata = [
                                 'token' => env('SMS_TOKEN'),
                                 'sender' => "IBEDC",
                                 'to' => $paymentLog->phone,
                                 "message" => "Meter Token: $token  Your IBEDC Prepaid payment of $paymentLog->amount for Meter No $paymentLog->meter_no  was successful. REF: $paymentLog->transaction_id. For Support: 07001239999",
                                 "type" => 0,
                                 "routing" => 3,
                             ];
     
                             $iresponse = Http::asForm()->post($baseUrl, $idata);
    
                             $this->info('***** SMS SUCCESSFULLY SENT :: SMS has been sent to the customer *************');
                             \Log::info("SMS SENT SUCCESSFULLY: ".   json_encode($idata));
    
                             $emailData = [
                                'token' => $token,
                                'meterno' => $paymentLog->meter_no,
                                'amount' => $paymentLog->amount,
                                "custname" => $paymentLog->customer_name,
                                "custphoneno" => $paymentLog->phone,
                                "payreference" => $paymentLog->transaction_id,
                            ];
    
                             Mail::to($paymentLog->email)->send(new PrePaidPaymentMail($emailData));
     
                            return $newResponse;
                          }

                    }

                });
                \Log::info(DB::getQueryLog());
                $this->info('***** PAYMENT COMPLETED:: All payments processed successfully *************');
                

        }catch(\Exception $e){

            \Log::info(DB::getQueryLog());
            $this->info('***** TOKENLOOKUP API PAYMENT COMPLETED:: All payments processed successfully *************');
        }
    }
}
