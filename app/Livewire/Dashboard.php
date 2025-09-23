<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transactions\PaymentTransactions;
use App\Models\User;
use App\Models\ContactUs;
use App\Models\Transactions\PayTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Session;
use App\Services\PolarisLogService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Mail\PrePaidPaymentMail;
use App\Services\AuditLogService;
use App\Models\Wallet\WalletHistory;
use Mail;


class Dashboard extends Component
{

    public $transactions;
    public $count_transactions;
    public $users;
    public $complaints;
    public $all_transactions;
    public $today_transactions;
    public $monthlyCollection;
    public $totalCollection;
    public $clearOption;
    public $clearValue;

    public function mount()
    {

        $user = Auth::user();

         if ($user->default_password == 1 || $user->default_password == "1") {
             // Redirect to dashboard if password already changed
            return redirect()->route('change_password');
        }

        if($user->authority == (RoleEnum::agency_admin()->value )) {
          //redirect to agency dashboard
          return redirect()->route('agency_dashboard');
        } 

        if($user->authority == (RoleEnum::user()->value)  || $user->authority == (RoleEnum::supervisor()->value)
        
        || $user->authority == (RoleEnum::bhm()->value) || $user->authority == (RoleEnum::dtm()->value)
        || $user->authority == (RoleEnum::region()->value) || $user->authority == (RoleEnum::billing()->value)
        ) {
           abort(403, 'Unathorized action. You do not have access to this page');
        } 

        $today = now()->toDateString();
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->endOfMonth()->toDateString();


        $transaction = new PaymentTransactions();
        $this->transactions = $transaction->sumTodaySales();

        /////////////// TODAY'S COLLECTION ///////////////////////
        $today_ibedcv2 = $transaction->whereDate('created_at', $today)
        ->whereIn('status', ['success', 'processing'])
        ->whereNotNull('providerRef')
        ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(10, 2))) AS DECIMAL(10, 2)) AS sum_amount'))
        ->first()
        ->sum_amount;

        /////////////// MONTHLY COLLECTION ///////////////////////
        $monthlyCollectionv2 = $transaction->whereBetween('created_at', [$startDate, $endDate])
        ->whereIn('status', ['success', 'processing'])
        ->whereNotNull('providerRef')
        ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(18, 2))) AS DECIMAL(18, 2)) AS sum_amount'))
        ->first()
        ->sum_amount;

        //////////////// TOTAL IBEDC COLLECTION /////////////////////////////////////
        $totalCollectionv2 = $transaction->whereIn('status', ['success', 'processing'])
            ->whereNotNull('providerRef')
            ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(18, 2))) AS DECIMAL(18, 2)) AS sum_amount'))
            ->first()
            ->sum_amount;


 /////////////////////////////////////////////////////////// IBEDC VERSION 1//////////////////////////////////////////////////////V1ibedc Pay
        $today_ibedcv1 = PayTransactions::whereDate('created_at', $today)
        ->whereIn('status', ['pending', 'success'])
        ->whereNotNull('providerRef')
        ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(10, 2))) AS DECIMAL(10, 2)) AS sum_amount'))
        ->first()
        ->sum_amount;

        $monthlyCollectionv1 = PayTransactions::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'success'])
            ->whereNotNull('providerRef')
            ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(18, 2))) AS DECIMAL(18, 2)) AS sum_amount'))
            ->first()
            ->sum_amount;

        
        $totalCollectionv1 = PayTransactions::whereIn('status', ['pending', 'success'])
            ->whereNotNull('providerRef')
            ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(18, 2))) AS DECIMAL(18, 2)) AS sum_amount'))
            ->first()
            ->sum_amount;

        $this->today_transactions =  $today_ibedcv2 + $today_ibedcv1;

        $this->monthlyCollection = $monthlyCollectionv2 +  $monthlyCollectionv1;

        $this->totalCollection  =  $totalCollectionv2 + $totalCollectionv1;

        //User Information on Dashboard
        $this->users = User::userCountFormatted(); // Call the static method directly on the User model
        $this->complaints = ContactUs::userComplains(); // Call the static method directly on the ContactUs model

        //All Transactions
        $this->all_transactions = $transaction->whereIn('status', ['processing', 'failed', 'started'])->orderby('created_at', 'desc')->limit(50)->get();

        


    }


    public function searchTransactions() {

        if (!$this->clearOption) {
            session()->flash('error', 'Please select an option');
            return;
        }


        if ($this->clearOption && $this->clearValue) {
            $option = $this->clearOption;
            $value = $this->clearValue;
        }

        $this->all_transactions = PaymentTransactions::query()
        ->when($this->clearOption && $this->clearValue, function ($query) {
            $query->where($this->clearOption, '=', $this->clearValue);
        })
       ->orderByDesc('created_at') // Assuming 'created_at' is the column you want to order by
        ->get();

        if ($this->all_transactions->isEmpty()) {
            $this->all_transactions = collect();
        }


    }


      public function processTransaction($id){
        
        $user = Auth::user();


        if($user->authority != "super_admin" && $user->authority != "payment_channel" ){
            Session::flash('error', 'You do not have access to this function');
            return;
        }
        
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


        $providerKey = $this->transactions->provider === 'Polaris' ?  env("FLUTTER_POLARIS_KEY") :  env('FLUTTER_FCMB_KEY'); // : env("FLUTTER_POLARIS_KEY");

        //Before the proceed to process the token
        $flutterData = [
            'SECKEY' => $providerKey, // env("FLUTTER_POLARIS_KEY"), // 'FLWSECK-d1c7523a58aad65d4585d47df227ee25-X',
            "txref" => $this->transactions->transaction_id
        ];

       

        $flutterUrl = env("FLUTTER_WAVE_URL");

        $iresponse = Http::post($flutterUrl, $flutterData);
        $flutterResponse = $iresponse->json(); 

        if (isset($flutterResponse['status']) && $flutterResponse['status'] == "success" &&  $flutterResponse['data']['status'] == 'successful' ) {


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
               //  return redirect()->route('dashboard');
                 
            } else {
               
                Session::flash('error', json_encode($newResponse));
                
                $errorMessage = json_encode($newResponse);
                return view('livewire.transaction-details', compact('errorMessage'));
                
            }

            $audit_description = "Syncing Payment for ". $this->transactions->transaction_id. " => ".  json_encode($emailData);
          //  AuditLogService::logAction('Sync Payment', $this->user->authority,  $audit_description, $this->user->id, 200);

        } else {

           // dd($flutterResponse);
           // Session::flash('error', $flutterResponse['data']['status']);
            if(isset($flutterResponse['data']['message'])) {
                Session::put('error', $flutterResponse['data']['message']);
            } else {
                //dd($flutterResponse);
                if( $flutterResponse == "null"){
                    Session::flash('error', "No Response - Null");
                }else {
                    Session::flash('error', $flutterResponse);
                }
                
            }

          //  $audit_description = "Syncing Payment for ". $this->transactions->transaction_id. " => ".  $flutterResponse['data']['message'];
           // AuditLogService::logAction('Sync Payment', $this->user->authority,  $audit_description, $this->user->id, 200);

        }


    }







       public function processWalletTransaction($id) {

        $user = Auth::user();


        if($user->authority != "super_admin" ){
            Session::flash('error', 'You do not have access to this function');
            return;
        }
        
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


      $checkWalletHistory = WalletHistory::where("provider_reference", $this->transactions->providerRef)->first();


        if ( $checkWalletHistory) {


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
                 //return redirect()->route('dashboard');
                 
            } else {
               
                Session::flash('error', json_encode($newResponse));
                
                $errorMessage = json_encode($newResponse);
                //return view('livewire.transaction-details', compact('errorMessage'));
                
            }

            

        } else {

           // dd($flutterResponse);
            Session::put('error', "Error Processing Wallet Transaction");
            // if(isset($flutterResponse['data']['message'])) {
            //     Session::put('error', $flutterResponse['data']['message']);
            // } else {
            //     Session::flash('error', $flutterResponse['data']['status']);
            // }
        }

    }



    public function render()
    {
        return view('livewire.dashboard');
    }
}
