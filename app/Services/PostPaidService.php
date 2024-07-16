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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
            'receiptno' => isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] :  '',
        ]);
        dispatch(new PostPaidJob($checkRef));


        //COMMISSION CALCULATION FOR AGENTS
        $user_authority = Auth::user();

        if (in_array($custInfo->TariffID, [1, 4, 6, 7, 9, 11, 13, 16, 19]) &&  $user_authority->authority == 'agent' ) {
            // Your commission calculation logic here
            $this->calculateCommission($checkRef);
        }
        
        
        \Log::info('Postpaid Payment Successful Response: ' . json_encode($newResponse));

        return $this->sendSuccess($checkRef, "Payment Successfully Completed", Response::HTTP_OK);

    } else {
        // $update = PaymentTransactions::where("transaction_id", $checkRef->transaction_id)->update([
        //     'status' =>  'processing', //"resp": "00",
        // ]);
        return $this->sendSuccess($checkRef, "Payment Successfully Completed", Response::HTTP_OK);
    }

   }



   private function calculateCommission($checkRef){

    //check the type of payment, if the payment is for the current charge.
    if($checkRef->payment_source == "current_charge"){

         //if no outstandin balance give agent 0.5 percent of the amount and create a commission record and add the agent wallet
          // If no outstanding balance, give agent 0.5 percent of the amount
        // if ($checkRef->outstanding_balance == 0) {
        //     $commission = $checkRef->amount * 0.005; // 0.5% commission
        //     $this->createCommissionRecord($checkRef->agent_id, $commission);
        //     $this->addToAgentWallet($checkRef->agent_id, $commission);
        // }

    }

     // check if the customer have an outstanding balance;
    if($checkRef->payment_source == "oustanding_balance"){
        
        //if the customer have outstanding balance and have not paid consistency for the past 3 months and its a NMD customer calculate the payment for the 3 months
        // remove the difference from the oustanding_balance which in this case is the amount.

        // then pay the agent 5% of the difference
    }


   }



}
