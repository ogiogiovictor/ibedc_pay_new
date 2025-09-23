<?php

namespace App\Http\Controllers\NAC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AccountCreationRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Models\NAC\AccoutCreaction;
use App\Models\NAC\UploadAccountCreation;
use App\Models\NAC\ContinueAccountCreation;
use App\Http\Requests\ContinueCustomerRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UploadRequest;
use App\Http\Requests\FinalCustomerRequest;
use App\Models\NAC\Regions;
use App\Models\NAC\DSS;
use App\Models\NAC\UploadHouses;
use App\Jobs\TrackingIDJob;
use App\Jobs\NotificationJob;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\EMS\ZoneCustomers;
use App\Models\ECMI\EcmiCustomers;
use App\Models\EMS\BusinessUnit;
use App\Models\ECMI\NewTarrif;
use Illuminate\Support\Facades\Auth;
use App\Models\OustsourceEmail;
use Illuminate\Support\Facades\Mail;


class NewAccountUpload extends BaseAPIController
{
    public function validateUser(Request $request){

         $request->validate([
            'email' => 'required|string|email', 
        ]);

        //Check if email exists in our database, if not return error  ibedcoutsource.com
         $email = strtolower(trim($request->email));

         // Check domain manually
        if (!preg_match('/^[A-Za-z0-9._%+-]+@(ibedc\.com|ibedcoutsource\.com)$/i', $email)) {
            return $this->sendError('Only ibedc.com or ibedcoutsource.com.com emails are allowed', 'ERROR', Response::HTTP_FORBIDDEN);
        }


        //if exists return back the email and token code that u have updated in along side the email
         $token = random_int(100000, 999999);

         $data = [
            'email' => $request->email,
            'token' => $token
         ];

         //update or insert token
         $record = OustsourceEmail::updateOrCreate(
            ['EMAIL_ADDRESS' => $email],   // condition to check
            ['CODE' => $token],    // field(s) to update or insert
           // ['EMAIL_ADDRESS' => $email]
        );

         // Send email with token
        Mail::raw("Your verification code is: {$token}", function ($message) use ($email) {
            $message->to($email)
                    ->subject('Your Verification Code');
        });

         //Then save the token against the user email
         return $this->sendSuccess([
                   'data' => $data,
                ], 'DTE Login Information', Response::HTTP_OK);

    }


    public function pendingUpload(Request $request) {

          $request->validate([
            'email' => 'required|string|email',
            'code' => 'required', 
            'tracking_id' => 'required', 
        ]);

        //validate code against the email 
        $check = OustsourceEmail::where([
            'EMAIL_ADDRESS' => $request->email,
            'CODE' => $request->code,
        ])->first();

        if (!$check) {
            return $this->sendError('Invalid Code Provided, Please check your email', 'ERROR', Response::HTTP_FORBIDDEN);
        }



        $data = UploadHouses::where("tracking_id", $request->tracking_id)->whereIn("status", ["0", "1"])->with('account')->paginate(10);

        return $this->sendSuccess([ 'accounts' => $data], 'CUSTOMER APPLICATION SUCCESSFUL SUBMITTED', Response::HTTP_OK);
    }



    public function getprepaidcustomers() {

        $customers = EcmiCustomers::paginate(30);
        return $this->sendSuccess($customers, 'ECMI CUSTOMER APPLICATION SUCCESSFUL SUBMITTED', Response::HTTP_OK);
    }

    public function getpostpaidcustomers() {

        $customers = ZoneCustomers::paginate(30);
        return $this->sendSuccess($customers, 'EMS CUSTOMER APPLICATION SUCCESSFUL SUBMITTED', Response::HTTP_OK);
        
    }
}
