<?php

namespace App\Http\Controllers\History;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EMS\ZoneCustomers;
use App\Models\EMS\ZonePayments;
use App\Models\ECMI\EcmiCustomers;
use App\Models\ECMI\EcmiPayments;
use App\Enums\TransactionEnum;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Http\Resources\PrepaidResource;
use App\Http\Resources\PostpaidResource;

class CustomerPaymentHistory extends BaseAPIController
{
    public function customerHistory(Request $request){

        if(!$request->type || !$request->customer_number){
            return $this->sendError('Error Verifying Payments', "Error!", Response::HTTP_BAD_REQUEST);
        }

        if($request->type == TransactionEnum::Prepaid()->value ){

            $checkifExist = EcmiCustomers::where("MeterNo", $request->customer_number)->first();

            if($checkifExist){
                $getTransaction = EcmiPayments::where("MeterNo", $request->customer_number)->orderby("TransactionDateTime", 'desc')->take(20)->get();
                return $this->sendSuccess( [
                    'payload' => PrepaidResource::collection($getTransaction),
                    'message' => 'Customer History Successfully Loaded',
                ], Response::HTTP_OK);

            } else {
                return $this->sendError('Error Verifying Payments', "Error!", Response::HTTP_BAD_REQUEST);
            }

        } else if($request->type == TransactionEnum::Postpaid()->value){

            $checkifExist = ZoneCustomers::where("AccountNo", $request->customer_number)->first();

            if($checkifExist){
                $getTransaction = ZonePayments::where("AccountNo", $request->customer_number)->orderby("DateEngtered", 'desc')->take(20)->get();
                return $this->sendSuccess( [
                    'payload' => PostpaidResource::collection($getTransaction),
                    'message' => 'Customer History Successfully Loaded',
                ], Response::HTTP_OK);

            } else {
                return $this->sendError('Error Verifying Payments', "Error!", Response::HTTP_BAD_REQUEST);
            }


        }else {

            return $this->sendError('Invalid Payment Type', "Error!", Response::HTTP_BAD_REQUEST);

        }

      

    }
}
