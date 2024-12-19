<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Http;
use App\Models\ECMI\EcmiCustomers;
use App\Models\Transactions\PaymentTransactions;
use App\Jobs\PrepaidJob;
use App\Services\PolarisLogService;


class PrePaidService extends BaseAPIController
{
    public function processService($checkRef, $request, $payment)
    {

       
         $baseUrl = env('MIDDLEWARE_URL');
         $zoneECMI = EcmiCustomers::where("MeterNo", $request->account_id)->first();

        $checkExist = PaymentTransactions::where("transaction_id", $checkRef->transaction_id)->value("receiptno");

         //The log the payment response first
         if($request->provider == "Polaris") {
            (new PolarisLogService)->processLogs($checkRef->transaction_id, $request->account_id,  $zoneECMI->AccountNo, $payment);
         }
        

        //if($checkExist && $checkExist != "NULL"){
        if($checkExist){

            return $this->sendSuccess($checkExist, "PaymentSource Successfully Loaded", Response::HTTP_OK);

        } else {

            $payment = [
                'meterNo' => $request->account_id,
                'account_type' => $request->account_type,
                'amount' => $request->amount, //$payment['data']['amount'],
                'disco_name' => "IBEDC",
                'customerName' => $zoneECMI->Surname. ' '. $zoneECMI->OtherNames,
                'BUID' => $zoneECMI->BUID,
                'phone' => $request->phone,
                'transaction_id' => $checkRef->transaction_id,
                'email' => $checkRef->email,
                'id' => $checkRef->id
            ];

            // $update = PaymentTransactions::where("transaction_id", $checkRef->transaction_id)->update([
            //     'response_status' => 1,
            //     'status' => "processing",
            //     'Descript' => 'Processing, Your token is underway'
            // ]);

            if (!str_starts_with($checkRef->email, 'default')) {
                dispatch(new PrepaidJob($payment));
            } 

            return $this->sendSuccess($payment, "Payment Successfully Token will be sent to your email", Response::HTTP_OK);


        }

      
    }
}
