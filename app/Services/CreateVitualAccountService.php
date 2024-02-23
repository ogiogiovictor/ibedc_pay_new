<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Http;
use App\Helpers\StringHelper;
use App\Jobs\VirtualAccountJob;
use App\Models\VirtualAccount;
use Illuminate\Support\Facades\Log;

class CreateVitualAccountService
{
    public function createAccount($user)
    {

        $requestRef = StringHelper::generateUUIDReference();
        $signauture = $requestRef.";CQcQrCeeLdmkP5ME"; //env('POLARIS_VIRTUAL_SECURE');
        $baseURL = "https://api.openbanking.vulte.ng/v2/transact";
        
        \Log::info('Virtual Account Signature: ' .  $baseURL);

        $getData = $this->polarisdata($user, $requestRef);

        \Log::info('Request Data: ' . json_encode($getData));
       
        $newResponse = Http::withoutVerifying()->withHeaders([ 
            "Authorization" => "Bearer ta0bfAcIrJYm72g7uVKl_7c911c9e6ed64509b49498b7f94eb06b", 
            "Signature" => md5($signauture),
        ])->post($baseURL, $getData);

        // //Http::withoutVerifying()->withHeaders([  Http::withHeaders([

         $response =  $newResponse->json();

        \Log::info('Request URL: ' . "https://api.openbanking.vulte.ng/v2/transact");
        \Log::info('Request Headers: ' . json_encode([
            'Authorization' => "Bearer ta0bfAcIrJYm72g7uVKl_7c911c9e6ed64509b49498b7f94eb06b",
            "Signature" => md5($signauture)
        ]));
        
        \Log::info('Virtual Account Response: ' . json_encode($response));

        if($response['status'] == "Successful") {

            VirtualAccount::create([
                'transaction_ref' => $response['data']['provider_response']['reference'], 
                'account_no' => $response['data']['provider_response']['account_number'], 
                 'contract_code' => $response['data']['provider_response']['contract_code'], 
                 'account_reference' => $response['data']['provider_response']['account_reference'], 
                 'account_name'=> $response['data']['provider_response']['account_name'],  
                 'customer_email' => $response['data']['provider_response']['customer_email'],   
                 'bank_name' => $response['data']['provider_response']['bank_name'],   
                'bank_code' => $response['data']['provider_response']['bank_code'],   
                'account_type' => $response['data']['provider_response']['account_type'],   
                'status' => $response['data']['provider_response']['status'],  
                'user_id' => $user->id
            ]);

            dispatch(new VirtualAccountJob($response, $user));

        }

       
    }


    private function polarisdata($user, $requestRef){

        $nameArray = explode(' ', $user->name);
        
        return [
            "request_ref" => $requestRef,
            "request_type" => "open_account",
            "auth" => [
                "type" => null,
                "secure" => null,
                "auth_provider" => "PolarisVirtual",
                "route_mode" => null
            ],
            "transaction" => [
                "mock_mode" => "Inspect",
                "transaction_ref" => StringHelper::generateTransactionReference(),
                "transaction_desc" => "Creation of Virtual Account For". $user->name,
                "transaction_ref_parent" => null,
                "amount" => 0,
                "customer" => [
                    "customer_ref" => $user->user_code,
                    "firstname" => $nameArray[0],
                    "surname" => $nameArray[1],
                    "email" => $user->email,
                    "mobile_no" => $user->phone
                ],
                "meta" => [
                    "a_key" => $user->id,
                    "b_key" => $user->meter_no_primary
                ],
                "details" => [
                    "name_on_account" => "",
                    "middlename" => $user->name,
                    "dob" => "",
                    "gender" => "",
                    "title" => "",
                    "address_line_1" => "",
                    "address_line_2" => "",
                    "city" => "",
                    "state" => "",
                    "country" => ""
                ]
            ]
        ];
    }



}
