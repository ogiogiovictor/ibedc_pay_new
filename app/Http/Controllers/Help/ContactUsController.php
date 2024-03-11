<?php

namespace App\Http\Controllers\Help;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Events\ContactUsEvent;
use Illuminate\Support\Facades\Auth;
use App\Models\ContactUs;
use Illuminate\Support\Facades\Http;

class ContactUsController extends BaseAPIController
{
    public function index(){

    }

    public function store(Request $request){

        $validate = $request->validate([
            'subject' => 'required',
            'message' => 'required',
            'unique_code' => 'required',
            'account_type' => 'required' 
        ]);

        $createResponse = ContactUs::create([
            'name' => Auth::user()->name,
            'message' => $validate['message'],
            'subject' => $validate['subject'],
            'phone' => Auth::user()->phone,
            'email' => Auth::user()->email,
            'account_type' => $validate['account_type'],
            'unique_code' => $validate['unique_code'],
            'status' => 1,
        ]);


        $idata = [
            "account_no" => $request->unique_code,
            "classification" => "complain",
            "content" =>  $request->message
        ];

        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => env('CRM_TOKEN'),
        ])->post(env('CRM_URL'), $idata);


        event(new ContactUsEvent($createResponse));
      

        return $this->sendSuccess($createResponse, "Successfully Sent", Response::HTTP_OK);



    }
}
