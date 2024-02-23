<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Http;
use App\Helpers\StringHelper;
use App\Jobs\VirtualAccountJob;
use App\Models\VirtualAccount;

class CreateVitualAccountService
{
    public function createAccount($user)
    {

        $requestRef = StringHelper::generateUUIDReference();
        $signaute = $requestRef.";".env('POLARIS_VIRTUAL_SECURE');

        $getData = $this->polarisdata($user, $requestRef);

        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => env('POLARIS_VIRTUAL'),
            "Signature" => md5($signaute)
        ])->post(env('POLARIS_VIRTUAL_URL'), $getData);

        if($response->status == "Successful") {

            VirtualAccount::create([
                'transaction_ref' => $response->data->provider_response->reference, 
                'account_no' => $response->data->provider_response->account_number, 
                 'contract_code' => $response->data->provider_response->contract_code, 
                 'account_reference' => $response->data->provider_response->account_reference, 
                 'account_name'=> $response->data->provider_response->account_name,  
                 'customer_email' => $response->data->provider_response->customer_email,   
                 'bank_name' => $response->data->provider_response->bank_name,   
                'bank_code' => $response->data->provider_response->bank_code,   
                'account_type' => $response->data->provider_response->account_type,   
                'status' => $response->data->provider_response->status,  
                'user_id' => $user->id
            ]);

            dispatch(new VirtualAccountJob($getData, $user));

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
