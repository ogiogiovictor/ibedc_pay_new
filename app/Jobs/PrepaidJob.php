<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;
use App\Mail\PrePaidPaymentMail;
use Illuminate\Support\Facades\Http;
use App\Models\Transactions\PaymentTransactions;
use Illuminate\Support\Facades\Log;
use App\Models\ECMI\EcmiPayments;

class PrepaidJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payment;
    /**
     * Create a new job instance.
     */
    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $baseUrl = env('MIDDLEWARE_URL');
        $addCustomerUrl = $baseUrl . 'vendelect';

        $data = [
            'meterno' => $this->payment['meterNo'],
            'vendtype' => $this->payment['account_type'],
            'amount' => $this->payment['amount'], 
            "provider" => $this->payment['disco_name'],
            "custname" => $this->payment['customerName'],
            "businesshub" => $this->payment['BUID'],
            "custphoneno" => $this->payment['phone'],
            "payreference" => $this->payment['transaction_id'],     // StringHelper::generateTransactionReference(),
            "colagentid" => "IB001",
            "email" => $this->payment['email'],

        ];

        $checkifTokenExist = PaymentTransactions::where("transaction_id", $this->payment['transaction_id'])->first();


        if($checkifTokenExist->status == 'processing' && $checkifTokenExist->providerRef != "" && $checkifTokenExist->receiptno == 'NULL' ){

            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => env('MIDDLEWARE_TOKEN'),
            ])->post($addCustomerUrl, $data);

            $newResponse =  $response->json();

           
                //Post to Middleware and confirm succesful status
                if (isset($newResponse['status']) && $newResponse['status'] == "true") 
                {

                    //$newResponse['transactionReference']
                    $update = PaymentTransactions::where("transaction_id", $this->payment['transaction_id'])->update([
                        'status' => $newResponse['status'] == "true" ?  'success' : 'failed', //"resp": "00",
                        'receiptno' =>   isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'],  //Carbon::now()->format('YmdHis').time()
                        'Descript' =>  isset($newResponse['message']) ? $newResponse['message'] : $newResponse['transactionStatus'],
                        'units' => isset($newResponse['Units']) ? $newResponse['Units'] : $newResponse['data']['Units'], 
                        'minimumPurchase' => $newResponse['customer']['minimumPurchase'],
                        'tariffcode'  => $newResponse['customer']['tariffcode'],
                        'customerArrears' => $newResponse['customer']['customerArrears'],
                        'tariff' => $newResponse['customer']['tariff'],
                        'serviceBand' => $newResponse['customer']['serviceBand'],
                        'feederName' => $newResponse['customer']['feederName'],
                        'dssName' => $newResponse['customer']['dssName'],
                        'udertaking' => $newResponse['customer']['undertaking'],
                        'VAT' =>  EcmiPayments::where("transref", $newResponse['transactionReference'])->value('VAT'),
                        'costOfUnits' => EcmiPayments::where("transref", $newResponse['transactionReference'])->value('CostOfUnits'),
                    ]);

                     //Send SMS to User
                     $token =  isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'];

                     $baseUrl = env('SMS_MESSAGE');
                     $amount = $this->payment['amount'];
                     $transactionID = $this->payment['transaction_id'];
                     $meterNo = $this->payment['meterNo'];
 
                     $smsdata = [
                         'token' => "p42OVwe8CF2Sg6VfhXAi8aBblMnADKkuOPe65M41v7jMzrEynGQoVLoZdmGqBQIGFPbH10cvthTGu0LK1duSem45OtA076fLGRqX",
                         'sender' => "IBEDC",
                         'to' => $this->payment['phone'],
                         "message" => "Meter Token: $token Your payment of $amount for Meter No $meterNo was successful. REF: $transactionID. For Support: 07001239999",
                         "type" => 0,
                         "routing" => 3,
                     ];
 
                     Log::info('NULL RESPONSE: - ', ['SMS Response' =>    $smsdata ]);

                     $emailData = [
                        'token' => $token,
                        'meterno' => $this->payment['meterNo'],
                        'amount' => $this->payment['amount'], 
                        "custname" => $this->payment['customerName'],
                        "custphoneno" => $this->payment['phone'],
                        "payreference" => $this->payment['transaction_id'],    
                    ];

                    Log::info('TOKEN SENT: : - ', ['Generated Successfully' =>     $smsdata ]);

                    $iresponse = Http::asForm()->post($baseUrl, $smsdata);
                    //Send a Successfully Mail to user
                    Mail::to($this->payment['email'])->send(new PrePaidPaymentMail($emailData));


                } else {
        
                    Log::info('MIDDLEWARE RESPONSE: - ', ['Middleware Response' =>     $newResponse ]);
                }


        } else {

            Log::info('IBEDCPAY RESPONSE: - ', ['IBEDC-PAY Response' =>     $checkifTokenExist ]);
        }

    }
}
