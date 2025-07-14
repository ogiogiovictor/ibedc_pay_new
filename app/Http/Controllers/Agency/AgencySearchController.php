<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agency\Agents;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Models\EMS\ZoneCustomers;
use App\Models\ECMI\EcmiCustomers;
use App\Http\Resources\CustomerResource;
use App\Models\ECMI\EcmiPayments;
use App\Models\EMS\ZonePayments;


class AgencySearchController extends BaseAPIController
{
    public function searchCustomers(Request $request){

        if(!$request->account_type || !$request->account_id){
            return  $this->sendError("Important Params Missing",  "ERROR!", Response::HTTP_BAD_REQUEST);  
        }
 
        switch($request->account_type){
             case 'Prepaid':
                return $this->prepaidServices($request);
             case 'Postpaid':
                return $this->postpaidServices($request);
             default:
                 throw new \InvalidArgumentException('Invalid type');  
        }
 
    }


    private function prepaidServices($request){

       
        try{

            $returnRequest = ECMICustomers::where("MeterNo", $request->account_id)->firstOrFail();

            $data = [
                'customer-info' => $returnRequest,
                'payments' => EcmiPayments::where("MeterNo", $request->account_id)->limit(10)->orderBy('TransactionDateTime', 'desc')->get(),
            ];
           
            return $this->sendSuccess($data, "SUCCESS", Response::HTTP_OK);

        }catch(\Exception $e) {  //DatabaseException
            return  $this->sendError("Customer Record Not Found",  "ERROR!", Response::HTTP_NOT_FOUND);   
        }
       

    }

    private function postpaidServices($request){

        try {
            $customers = ZoneCustomers::where("AccountNo", $request->account_id)->firstOrFail();

             // Get latest payment
            // $latestPayment = ZonePayments::where('AccountNo', $customer->AccountNo)
            //     ->orderByDesc('PayDate')
            //     ->first();

            // $customer->lastpaymentday = $latestPayment->PayDate ?? null;
            // $customer->lastpayment = $latestPayment->Payments ?? null;

            // // Get minimum purchase via external API
            // $customer->customerArrears = $this->getMinimumPurchase($customer->AccountNo);


            $data = [
                'customer-info' => $customers,
                'payments' => ZonePayments::where("AccountNo", $request->account_id)->limit(10)->orderBy('PayDate', 'desc')->get(),
            ];

            return $this->sendSuccess($data, "SUCCESS", Response::HTTP_OK);

        }catch(\Exception $e){
            return  $this->sendError("Customer Record Not Found",  "ERROR!", Response::HTTP_NOT_FOUND);   
        }
       

    }


     private function getMinimumPurchase($accountNo)
    {
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
            ])->post("https://middleware3.ibedc.com/api/v1/verifymeter", [
                'meter_number' => $accountNo,
                'vendtype' => 'Postpaid',
            ]);

            $data = $response->json();

            return $data['data']['customerArrears'] ?? 0;
        } catch (\Exception $e) {
            \Log::error("Minimum Purchase API error for {$accountNo}: " . $e->getMessage());
            return 0;
        }
    }
    
}
