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
use App\Models\EMS\BusinessUnit;
use App\Models\ECMI\NewTarrif;
use Illuminate\Support\Facades\Auth;


class NewAccountUpload extends BaseAPIController
{
    public function validateUser(Request $request){

         $request->validate([
            'email' => 'required|string|email', 
        ]);

        //Check if email exists in our database, if not return error
         // You can use App\Models\User or any custom NAC user model
        // $user = \App\Models\DTE::where('email', $email)->first();

        // if (!$user) {
            // return $this->sendError('No DTE with such information', 'ERROR', Response::HTTP_NOT_FOUND);
        // }


        //if exists return back the email and token code that u have updated in along side the email
         $token = random_int(100000, 999999);

         $data = [
            'email' => $request->email,
            'token' => $token
         ];

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


        $data = UploadHouses::where("tracking_id", $request->tracking_id)->whereIn("status", ["0", "1"])->with('account')->paginate(10);

        return $this->sendSuccess([ 'accounts' => $data], 'CUSTOMER APPLICATION SUCCESSFUL SUBMITTED', Response::HTTP_OK);
    }
}
