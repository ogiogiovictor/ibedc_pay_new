<?php

namespace App\Http\Controllers\History;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Models\EMS\ZoneBills;
use App\Enums\TransactionEnum;


class CustomerBillHistory extends BaseAPIController
{
    public function customerBills(Request $request) {

        if(!$request->type || !$request->customer_number){
            return $this->sendError('Error Verifying Bills', "Error!", Response::HTTP_BAD_REQUEST);
        }

        if($request->type == TransactionEnum::Postpaid()->value ){

            $checkifExist = ZoneBills::where("AccountNo", $request->customer_number)->orderby("Billdate", "desc")->paginate(30);

            if($checkifExist){

                return $this->sendSuccess( [
                    'payload' => $checkifExist,
                    'message' => 'Customer Bills Successfully Loaded',
                ], Response::HTTP_OK);

            } else {
                return $this->sendError('No Bill History', "Error!", Response::HTTP_BAD_REQUEST);
            }


        } else {
            return $this->sendError('Invalid Parameter Provided', "Error!", Response::HTTP_BAD_REQUEST);
        }

    }
}
