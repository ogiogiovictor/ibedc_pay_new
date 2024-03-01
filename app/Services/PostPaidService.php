<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Http;
use App\Models\EMS\ZoneCustomers;
use App\Models\EMS\BusinessUnit;
use App\Models\Transactions\PaymentTransactions;
use Carbon\Carbon;
use App\Jobs\PostPaidJob;

class PostPaidService extends BaseAPIController
{
   public function processService($checkRef, $request, $payment){

     $baseUrl = env('MIDDLEWARE_URL');
     $custInfo = ZoneCustomers::where("AccountNo", $request->account_id)->first();
     $buCode = BusinessUnit::where("BUID", $custInfo->BUID)->value("Name");
     
     $addCustomerUrl = $baseUrl . 'vendelect';


     $data = [
        'meterno' => $request->account_id,
        'vendtype' => $checkRef->account_type,
        'amount' => $request->amount, //$payment['data']['amount'], 
        "custname" => $checkRef->customer_name,
        "businesshub" => isset($buCode) ? $buCode : $custInfo->BUID,
        "custphoneno" => $request->phone,
        "payreference" => $checkRef->transaction_id,
        "colagentid" => "IB001",
                         
    ];

    $response = Http::withoutVerifying()->withHeaders([
        'Authorization' => env('MIDDLEWARE_KEY'),
    ])->post($addCustomerUrl, $data);

    $newResponse =  $response->json();


    if($newResponse['status'] == "true"){ 
        //Update the status of payment and send the job and send SMS
        $update = PaymentTransactions::where("transaction_id", $checkRef->transaction_id)->update([
            'status' =>  'success', //"resp": "00",
            'receiptno' =>  Carbon::now()->format('YmdHis'),
        ]);
        dispatch(new PostPaidJob($checkRef));

        return $this->sendSuccess($checkRef, "Payment Successfully Completed", Response::HTTP_OK);

    } else {
        $update = PaymentTransactions::where("transaction_id", $checkRef->transaction_id)->update([
            'status' =>  'processing', //"resp": "00",
        ]);
        return $this->sendSuccess($checkRef, "Payment Successfully Completed", Response::HTTP_OK);
    }

  

   }
}
