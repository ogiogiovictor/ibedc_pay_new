<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transactions\PayTransactions;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Mail;
use App\Mail\PrePaidPaymentMail;
use App\Models\ECMI\EcmiPayments;
use Illuminate\Support\Facades\Session;

class ViewTransaction extends Component
{
    public $transactions = [];

    public function mount() {

        $this->transactions = PayTransactions::where("transaction_id", $this->transactions)->first();

    }

    public function processTransaction($id){
        $this->transactions = PayTransactions::where("id", $id)->first();

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
            $update = PayTransactions::where("transaction_id", $this->transactions->transaction_id)->update([
                'status' => $newResponse['status'] == "true" ?  'success' : 'failed', //"resp": "00",
                'receiptno' =>   isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'],
                'Descript' =>  isset($newResponse['message']) ? $newResponse['message']."-".$newResponse['transactionReference'] : $newResponse['transaction_status']."-".$newResponse['transactionReference'],
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
                 $meter_no was successful. REF: $transactionID. For Support: 07001239999",
                "type" => 0,
                "routing" => 3,
            ];

            $iresponse = Http::asForm()->post($baseUrl, $idata);

            $emailData = [
                'token' => $token,
                'meterno' => $this->transactions->meter_no,
                'amount' => $this->transactions->amount,
                "custname" => $this->transactions->customer_name,
                "custphoneno" => $this->transactions->phone,
                "payreference" => $this->transactions->transaction_id,
            ];

             Mail::to($this->transactions->email)->send(new PrepaidPaymentMail($emailData));
             Session::flash('success', 'Token Sccessfully Sent');
             return redirect()->route('log_transactions');
             
        } else {
           
            Session::put('error', json_encode($newResponse));
            
            $errorMessage = json_encode($newResponse);
            return view('livewire.view-transaction', compact('errorMessage'));
            
        }

       // dd($this->transactions);
    }


    public function render()
    {
        return view('livewire.view-transaction');
    }
}
