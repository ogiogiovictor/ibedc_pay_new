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
           
            return $this->sendSuccess($returnRequest, "SUCCESS", Response::HTTP_OK);

        }catch(\Exception $e) {  //DatabaseException
            return  $this->sendError("Customer Record Not Found",  "ERROR!", Response::HTTP_NOT_FOUND);   
        }
       

    }

    private function postpaidServices($request){

        try {
            $returnRequest = ZoneCustomers::where("AccountNo", $request->account_id)->firstOrFail();

            return $this->sendSuccess($returnRequest, "SUCCESS", Response::HTTP_OK);

        }catch(\Exception $e){
            return  $this->sendError("Customer Record Not Found",  "ERROR!", Response::HTTP_NOT_FOUND);   
        }
       

    }
}
