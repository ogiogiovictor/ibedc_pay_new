<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;


class ArcGISValidation extends BaseAPIController
{
    public function getCustomer(Request $request) {

        if(!$request->account_type || !$request->account) {
            return $this->sendError('Please specify the account type or the account', "ERROR", Response::HTTP_BAD_REQUEST);
        }

        $data = [
            "meter_number" => $request->account,
            "vendtype" => $request->account_type
        ];

        
        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
        ])->post("https://middleware3.ibedc.com/api/v1/verifymeter", $data);

        $newResponse =  $response->json();

        return $this->sendSuccess($newResponse, "Transaction Successful", Response::HTTP_OK);

    }
}
