<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transactions\PaymentTransactions;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Mail;
use Illuminate\Support\Facades\Session;
use App\Mail\PrePaidPaymentMail;
use App\Models\ECMI\EcmiPayments;
use App\Services\PolarisLogService;

class TransactionDetails extends Component
{
    public $transaction_id;
    public $all_transactions;
    public $transactions;



    public function mount() {

        $transaction = new PaymentTransactions();
        $this->all_transactions = $transaction->where("transaction_id", $this->transaction_id)->first();

    }


    public function checkPaymentStatus($id) {

        //dd($this->transactions->transaction_id);

        $this->transactions = PaymentTransactions::where("id", $id)->first();

        if(!$this->transactions->transaction_id) { 
            Session::flash('error', 'Please provide a valid provider Reference');
            return redirect()->route('log_transactions');
        }

        $flutterData = [
            'SECKEY' =>  env("FLUTTER_POLARIS_KEY"), // FLWSECK-641d8833b7c2105ad0d38fbf7001cf13-18d7868ed56vt-X || 'FLWSECK-d1c7523a58aad65d4585d47df227ee25-X',
            "txref" => $this->transactions->transaction_id
        ];


        $flutterUrl = env("FLUTTER_WAVE_URL");

        $iresponse = Http::post($flutterUrl, $flutterData);
        $flutterResponse = $iresponse->json(); 

        //dd($iresponse);

        if (isset($flutterResponse['status']) && $flutterResponse['status'] == "success" && $flutterResponse['data']['status'] == 'successful' ) {
       // if (isset($flutterResponse['status']) && $flutterResponse['status'] == "success" && isset($flutterResponse['data']['status']) && $flutterResponse['data']['status'] == 'successful') {


            $update = PaymentTransactions::where("transaction_id", $this->transactions->transaction_id)->update([
                'providerRef' => $flutterResponse['data']['flwref'],
                'status' => 'processing'
            ]);

            Session::flash('success', $flutterResponse['data']['status']);
        } else {

            if(isset($flutterResponse['data']['message'])) {
                Session::flash('error', $flutterResponse['data']['message']);
            } else {
                Session::flash('error', isset($flutterResponse['data']['status']) ? $flutterResponse['data']['status'] : $iresponse);
            }
           
            
        }  
    }



    public function processTransaction($id){
        
        
        $this->transactions = PaymentTransactions::where("id", $id)->first();

        if(!$this->transactions->providerRef) { 
            Session::flash('error', 'Please provide a valid provider Reference');
            return;
           // return redirect()->route('dashboard');
        }

        if(!$this->transactions->status == 'processing') { 
            Session::flash('error', 'Transaction status cannot be verified, please check payment status');
            return;
            //return redirect()->route('log_transactions');
        }


        //Before the proceed to process the token
        $flutterData = [
            'SECKEY' =>  env("FLUTTER_POLARIS_KEY"), // 'FLWSECK-d1c7523a58aad65d4585d47df227ee25-X',
            "txref" => $this->transactions->transaction_id
        ];

       

        $flutterUrl = env("FLUTTER_WAVE_URL");

        $iresponse = Http::post($flutterUrl, $flutterData);
        $flutterResponse = $iresponse->json(); 

        if ($flutterResponse['status'] == "success" &&  $flutterResponse['data']['status'] == 'successful' ) {


            $baseUrl = env('MIDDLEWARE_URL');
            $addCustomerUrl = $baseUrl. 'vendelect';
    
            $data = [
                'meterno' => $this->transactions->meter_no,
                'vendtype' => $this->transactions->account_type,
                'amount' => $this->transactions->amount, 
                'provider' => "IBEDC",
                "custname" => $this->transactions->customer_name,
                "businesshub" => $this->transactions->BUID,
                "custphoneno" => $this->transactions->phone,
                "payreference" => $this->transactions->transaction_id,
                "colagentid" => "IB001",
                                 
            ];
    
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => env('MIDDLEWARE_TOKEN'), // 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
            ])->post($addCustomerUrl, $data);
    
            $newResponse =  $response->json();

            \Log::info('RESPONSE FROM MOMAS VIEW TRANSACTION: ' . json_encode($newResponse));

            if($newResponse['status'] == "true"){      
                $update = PaymentTransactions::where("transaction_id", $this->transactions->transaction_id)->update([
                    'status' => $newResponse['status'] == "true" ?  'success' : 'failed', //"resp": "00",
                    'receiptno' =>   isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'],
                    'Descript' =>  isset($newResponse['message']) ? $newResponse['message'] : $newResponse['transaction_status'],
                ]);
    
                 //Send SMS to User
                 $token =  isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'];
                 $baseUrl = env('SMS_MESSAGE');
    
                 $amount = $this->transactions->amount;
                 $meterno = $this->transactions->meter_no;
                 $transactionID = $this->transactions->transaction_id;
    
                 $idata = [
                    'token' => "p42OVwe8CF2Sg6VfhXAi8aBblMnADKkuOPe65M41v7jMzrEynGQoVLoZdmGqBQIGFPbH10cvthTGu0LK1duSem45OtA076fLGRqX",
                    'sender' => "IBEDC",
                    'to' => $this->transactions->phone,
                    "message" => "Meter Token: $token  Your IBEDC Prepaid payment of $amount for Meter No 
                     $meterno was successful. REF: $transactionID. For Support: 07001239999",
                    "type" => 0,
                    "routing" => 3,
                ];
    
             
    
               $emailData = [
                   'token' => $token,
                   'meterno' => $this->transactions->meter_no,
                   'amount' => $this->transactions->amount,
                   "custname" => $this->transactions->customer_name,
                   "custphoneno" => $this->transactions->phone,
                   "payreference" => $this->transactions->transaction_id,
                   "transaction_id" => $this->transactions->transaction_id,
               ];

               Mail::to($this->transactions->email)->send(new PrepaidPaymentMail($emailData));

               //$iresponse = Http::asForm()->post($baseUrl, $idata);

               //The log the payment response first
               (new PolarisLogService)->processLogs($this->transactions->transaction_id, 
               $this->transactions->meter_no,  $this->transactions->account_no, $flutterResponse);


                try {
                    // HTTP request with increased timeout and retry mechanism
                    $iresponse = Http::asForm()
                        ->timeout(30)  // timeout set to 30 seconds
                        ->retry(3, 100)  // retries 3 times with a 100ms delay
                        ->post($baseUrl, $idata);
                } catch (RequestException $e) {
                    // Log the error or handle the timeout exception
                    \Log::error('HTTP Request failed: ' . $e->getMessage());
                }


               
                 Session::flash('success', 'Token Sccessfully Sent');
                 return redirect()->route('dashboard');
                 
            } else {
               
                Session::flash('error', json_encode($newResponse));
                
                $errorMessage = json_encode($newResponse);
                return view('livewire.transaction-details', compact('errorMessage'));
                
            }

            

        } else {

           // dd($flutterResponse);
           // Session::flash('error', $flutterResponse['data']['status']);
            if(isset($flutterResponse['data']['message'])) {
                Session::flash('error', $flutterResponse['data']['message']);
            } else {
                Session::flash('error', $flutterResponse['data']['status']);
            }

        }




       
       

       // dd($this->transactions);
    }



    public function render()
    {
        return view('livewire.transaction-details');
    }
}
