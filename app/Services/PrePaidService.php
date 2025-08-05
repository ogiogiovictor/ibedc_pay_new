<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Http;
use App\Models\ECMI\EcmiCustomers;
use App\Models\Transactions\PaymentTransactions;
use App\Jobs\PrepaidJob;
use App\Services\PolarisLogService;
use App\Models\ECMI\EcmiPayments;
use Illuminate\Support\Facades\Auth;
use Mail;
use App\Mail\PrePaidPaymentMail;

class PrePaidService extends BaseAPIController
{
    public function processService($checkRef, $request, $payment)
    {

       
         $baseUrl = env('MIDDLEWARE_URL');
         $zoneECMI = EcmiCustomers::where("MeterNo", $request->account_id)->first();

        $checkExist = PaymentTransactions::where("transaction_id", $checkRef->transaction_id)->value("receiptno");

         //The log the payment response first
         if($request->provider == "Polaris") {
            (new PolarisLogService)->processLogs($checkRef->transaction_id, $request->account_id,  $checkRef->meter_no, $payment);
         }
        

        //if($checkExist && $checkExist != "NULL"){
        if($checkExist){

            return $this->sendSuccess($checkExist, "PaymentSource Successfully Loaded", Response::HTTP_OK);

        } else {  //[customer_name]

            $payment = [
                'meterNo' => $request->account_id,
                'account_type' => $request->account_type,
                'amount' => $request->amount, //$payment['data']['amount'],
                'disco_name' => "IBEDC",
                'customerName' =>  $checkRef->customer_name,   // $zoneECMI->Surname. ' '. $zoneECMI->OtherNames,
                'BUID' => $checkRef->BUID, //$zoneECMI->BUID,  //[BUID]
                'phone' => $request->phone,
                'transaction_id' => $checkRef->transaction_id,
                'email' => $checkRef->email,
                'id' => $checkRef->id
            ];

            $update = PaymentTransactions::where("transaction_id", $checkRef->transaction_id)->update([
                'provider' => $request->provider,
                'status' => "processing",
                'Descript' => 'Processing, Your token is underway'
            ]);

           // if (!str_starts_with($checkRef->email, 'default')) {
             //   dispatch(new PrepaidJob($payment));
            //} 

     
            try {

                 $data = [
                    'meterno' =>  $request->account_id,
                    'vendtype' => $request->account_type,
                    'amount' =>  $request->amount,
                    "provider" => "IBEDC",
                    "custname" => $checkRef->customer_name,
                    "businesshub" =>  $checkRef->BUID, 
                    "custphoneno" => $request->phone,
                    "payreference" => $checkRef->transaction_id,     // StringHelper::generateTransactionReference(),
                    "colagentid" => "IB001",
                    "email" => $checkRef->email,

                ];

                $baseUrl = env('MIDDLEWARE_URL');
                $addCustomerUrl = $baseUrl . 'vendelect';

                //  $response = Http::withoutVerifying()->withHeaders([
                //     'Authorization' => env('MIDDLEWARE_TOKEN'),
                // ])->post($addCustomerUrl, $data);

              $response = Http::withoutVerifying()
                ->timeout(15) 
                ->retry(3, 500) 
                ->withHeaders([
                    'Authorization' => env('MIDDLEWARE_TOKEN'),
                ])
                ->post($addCustomerUrl, $data);


                $newResponse =  $response->json();

                  if (isset($newResponse['status']) && $newResponse['status'] == "true") {

                     $update = PaymentTransactions::where("transaction_id", $checkRef->transaction_id)->update([
                        //'status' => $newResponse['status'] == "true" ?  'success' : 'failed', //"resp": "00",
                        'status' => 'success',
                        'receiptno' =>   isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'],  //Carbon::now()->format('YmdHis').time()
                        'Descript' =>  isset($newResponse['message']) ? $newResponse['message'] :  '', //$newResponse['transactionStatus'],
                        'units' => isset($newResponse['Units']) ? $newResponse['Units'] : $newResponse['data']['Units'], 
                        'minimumPurchase' => isset($newResponse['customer']['minimumPurchase']) ? $newResponse['customer']['minimumPurchase'] : '',
                        'tariffcode'  => isset($newResponse['customer']['tariffcode']) ? $newResponse['customer']['tariffcode'] : '',
                        'customerArrears' => isset($newResponse['customer']['customerArrears']) ? $newResponse['customer']['customerArrears'] : '',
                        'tariff' => isset($newResponse['customer']['tariff']) ? $newResponse['customer']['tariff'] :  '',
                        'serviceBand' => isset($newResponse['customer']['serviceBand']) ? $newResponse['customer']['serviceBand'] : '',
                        'feederName' => isset($newResponse['customer']['feederName']) ? $newResponse['customer']['feederName'] : '',
                        'dssName' => isset($newResponse['customer']['dssName']) ? $newResponse['customer']['dssName'] : '',
                        'udertaking' => isset($newResponse['customer']['undertaking']) ? $newResponse['customer']['undertaking'] : '',
                        'VAT' =>  EcmiPayments::where("transref", $newResponse['transactionReference'])->value('VAT'),
                        'costOfUnits' => EcmiPayments::where("transref", $newResponse['transactionReference'])->value('CostOfUnits'),
                    ]);

                     //Send SMS to User
                     $token =  isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'];

                      $emailData = [
                        'token' => $token,
                        'meterno' => $request->account_id,
                        'amount' =>  $request->amount,
                        "custname" => $checkRef->customer_name,
                        "custphoneno" => $request->phone,
                        "payreference" =>  $checkRef->transaction_id,
                    ];

                     $user = Auth::user();

                     if(isset($user->email) && $user->email != "" &&  $user->email != "null"){
                        Mail::to($user->email)->send(new PrePaidPaymentMail($emailData));
                    }


                    return $this->sendSuccess($payment, "Payment Successfully Token will be sent to your email", Response::HTTP_OK);

                  
                  } else  {

                     dispatch(new PrepaidJob($payment));
                      return $this->sendSuccess($payment, "Payment Successfully Token will be sent to your email", Response::HTTP_OK);

                  }

                

            }catch (\Exception $e) {
                // Timeout or network error → queue job
                dispatch(new PrepaidJob($payment));
                //dispatch(new PrepaidJob($paymentData));  //payment
                return $this->sendSuccess($payment, "Payment Successfully Token will be sent to your email", Response::HTTP_OK);
            }

           // return $this->sendSuccess($payment, "Payment Successfully Token will be sent to your email", Response::HTTP_OK);


        }

      
    }
}
