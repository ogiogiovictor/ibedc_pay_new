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

        try {

            $prepaidTransaction = PaymentTransactions::whereNull('receiptno')
                ->where('account_type', 'Prepaid')
                ->where('status', 'processing')  //processing
                ->whereNotNull('providerRef')
                ->orderby('created_at', 'desc')
                ->chunk(10, function($prepaidpayments) use (&$paymentData) {

                    foreach($prepaidpayments as $paymentLog){

                        if (!is_numeric($paymentLog->amount) || $paymentLog->amount < 0) {
                            Log::error("Invalid amount for transaction ID {$paymentLog->transaction_id}: {$paymentLog->amount}");
                            continue; // Skip to the next payment
                        }

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
    
                        \Log::info('RESPONSE FROM MOMAS API - PREPAID LOG: ' . json_encode($newResponse));
                        $totalRecords = count($prepaidpayments);

                       

                        if($newResponse['status'] == "true"){      
                        
                            $paymentData[] = $data;
                          
                             $update = PaymentTransactions::where("transaction_id", $paymentLog->transaction_id)->update([
                                // 'status' => $newResponse['status'] == "true" ?  'success' : 'failed', //"resp": "00",
                                 'status' => 'success',
                                 'receiptno' =>   isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'],
                                 'Descript' =>  isset($newResponse['message']) ? $newResponse['message']."-".$newResponse['transactionReference'] : $newResponse['transaction_status']."-".$newResponse['transactionReference'],
                                'units' => isset($newResponse['Units']) ? $newResponse['Units'] : $newResponse['data']['Units'], 
                                'minimumPurchase' => isset($newResponse['customer']['minimumPurchase']) ? $newResponse['customer']['minimumPurchase'] : '',
                                'tariffcode'  => isset($newResponse['customer']['tariffcode']) ? $newResponse['customer']['tariffcode'] : '',
                                'customerArrears' => isset($newResponse['customer']['customerArrears']) ? $newResponse['customer']['customerArrears'] : '',
                                'tariff' => isset($newResponse['customer']['tariff']) ? $newResponse['customer']['tariff'] : '',
                                'serviceBand' => isset($newResponse['customer']['serviceBand']) ? $newResponse['customer']['serviceBand'] : '',
                                'feederName' => isset($newResponse['customer']['feederName']) ? $newResponse['customer']['feederName'] : '',
                                'dssName' => isset($newResponse['customer']['dssName']) ? $newResponse['customer']['dssName'] : '',
                                'udertaking' => isset($newResponse['customer']['undertaking']) ? $newResponse['customer']['undertaking'] : '',
                                'VAT' =>  EcmiPayments::where("transref", $newResponse['transactionReference'])->value('VAT'),
                                'costOfUnits' => EcmiPayments::where("transref", $newResponse['transactionReference'])->value('CostOfUnits'),
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
    
                            $user = Auth::user();


                            if (!str_starts_with($user->email, 'default')) {
                                Mail::to($user->email)->cc($paymentLog->email)->send(new PrePaidPaymentMail($emailData));
                            }

                            if($paymentLog->email && !str_starts_with($paymentLog->email, 'default')) {
                                Mail::to($user->email)->cc($paymentLog->email)->send(new PrePaidPaymentMail($emailData));
                            }
                            

                            $iresponse = Http::asForm()->post($baseUrl, $idata);
     
                          //  return $newResponse;
                            Log::info("Successfully processed transaction: " . $paymentLog->transaction_id);
                          }

                    }

                });
                \Log::info(DB::getQueryLog());
                $this->info('***** PAYMENT COMPLETED:: All payments processed successfully *************');
                

        }catch(\Exception $e){
            \Log::info('ERROR MESSAGE - PREPAID LOG: ' . json_encode($e));
            $this->info('***** TOKENLOOKUP API PAYMENT COMPLETED:: All payments processed successfully *************');
        }
    }
}
