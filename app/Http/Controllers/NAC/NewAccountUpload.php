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
use Illuminate\Support\Facades\Http;
use App\Models\NAC\ServiceAreaCode;


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



        $data = UploadHouses::with(['landlordinfo', 'account'])->where("tracking_id", $request->tracking_id)->whereIn("status", ["0", "1"])->with('account')->paginate(10);

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





     public function syncAccount(Request $request){
        $validate = $request->validate([
            'account_no' => 'required',
        ]);

            
        $pendingAccounts = UploadHouses::where("status", 4)
                                           // ->where("duplicate", "0")
                                            ->where("account_no", $validate['account_no'])
                                            ->first();

   // return $pendingAccounts;                      
         if (!$pendingAccounts) {
          return $this->sendError("No pending accounts found.", [], Response::HTTP_NOT_FOUND);
        }

         $zoneCustomer =  ZoneCustomers::where("AccountNo", $pendingAccounts->account_no)->first();
        // return $this->sendSuccess($zoneCustomer, 'Zone Customer Data', Response::HTTP_OK);

          if(!$zoneCustomer){

                 $url = "http://192.168.15.157:9549/AccountGenerator/webresources/account/save/customer/114/FX321G9D"; // live API

                $landlordInfo = ContinueAccountCreation::where("tracking_id", $pendingAccounts->tracking_id)->first();

                // ✅ Use organisation_name if it's not null or empty, else use landlord_othernames
                if (!empty($landlordInfo->organisational_name)) {
                    $fname = "";
                    $sname = $landlordInfo->organisational_name; // No surname for organisations
                } else {
                    $fname = $landlordInfo->landlord_othernames;
                    $sname = $landlordInfo->landlord_surname;
                }

                 //$this->info("Account Number {$pending->account_no} Landlord Name. First Name: {$fname} Surname: {$sname}");

                 $servicecode =  $this->getAvailableServiceCode($pendingAccounts);

                 // 🔥 Skip account if BUID is missing
                if (!$servicecode || empty($servicecode->BUID)) {
                    return $this->sendError("BUID is NULL for Service Centre {$pendingAccounts->service_center} - Skipping account: {$pendingAccounts->account_no}", "ERROR", Response::HTTP_NOT_FOUND);
                
                }

                 $lat = substr($pendingAccounts->latitude ?? '', 0, 10);
                 $lng = substr($pendingAccounts->longitude ?? '', 0, 10);

                  $data = [
                    "accountNo" => $pendingAccounts->account_no,
                    "meterNo" => "",
                    "surname" =>  $sname,
                    "firstName" => $fname,
                    "otherNames" => "",
                    "email" => $landlordInfo->landlord_email ?? '',
                    "serviceAddress1" => ($pendingAccounts->house_no ?? '') . ' ' . ($pendingAccounts->full_address ?? ''),
                    "serviceAddress2" => $pendingAccounts->business_hub ?? '',
                    "serviceAddressCity" => $pendingAccounts->service_center ?? '',
                    "serviceAddressState" => $pendingAccounts->region ?? '',
                    "tariffID" => 1, //$pending->tarrif,
                    "arrears" => '',
                    "mobile" => $landlordInfo->landlord_telephone,
                    "gisCoordinate" => ($lat ?? '') . ',' . ($lng ?? ''),
                    "buid" => $servicecode->BUID ?? '',
                    "distributionID" => $pendingAccounts->dss,
                    "accessGroup" => "Administrator"
                ];

                


              
                $response = Http::post($url, $data);

              
                // Check if account number is already assigned to another customer
                $responseData = $response->json();

                  if (isset($responseData['code']) && $responseData['code'] == '400' ) {

                    return $this->sendError("Failed to create account for " . json_encode($response->json()), [], Response::HTTP_BAD_REQUEST);
                    //  $this->info("Payload: " . json_encode($data));
                    //  $this->info("Response: " . json_encode($response->json()));
                    // $this->info("Status: " . $response->status());
                        
                  }

                  if (isset($responseData['code']) && $responseData['code'] == '200' ) {

                   //  $this->info("Successful Response with Account Creation: " . json_encode($response->json()));

                    // 🔥 UPDATE duplication count once account is successfully created/sent
                    UploadHouses::where('id', $pendingAccounts->id)->update([
                        'duplicate' => 5
                    ]);

                    return $this->sendSuccess(json_encode($response->json()), "Successful Response with Account Creation: ", Response::HTTP_OK);
                        
                  }


                  //Check for successful account creation

            }

       

      //  return $this->sendSuccess($response->json(), "Successfully Sent", Response::HTTP_OK);
    }



       private function getAvailableServiceCode($uploadHouses)
    {

        $businessHub = trim($uploadHouses->business_hub);
        $serviceCenter = trim($uploadHouses->service_center);


      //  dd($businessHub, $serviceCenter);

         // Normalize business hub value
        if (strcasecmp($businessHub, 'Ijebu-Ode') === 0) {
            $businessHub = 'Ijebu';
        }

        if (strcasecmp($businessHub, 'Ijebu Igbo') === 0) {
            $businessHub = 'Ijebu';
        }

        if (strcasecmp($serviceCenter, 'Ijebu jesa') === 0) {
            $serviceCenter = 'IJEBU JESA';
        }

        // if (strcasecmp($businessHub, 'Ilesa') === 0) {
        //     $businessHub = 'ILESHA';
        // }

        //This is the problem wiht oke-soda comment later
         if (strcasecmp($businessHub, 'Ile-ife') === 0) {
            $businessHub = 'ILEIFE';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'AGO IWOYE') === 0) {
            $serviceCenter = 'AGO-IWOYE';
        }

        // Normalize business hub value
        if (strcasecmp($serviceCenter, 'ITA-OSHIN') === 0) {
            $serviceCenter = 'ITA OSHIN';
        }

        if (strcasecmp($serviceCenter, 'IMOWO II') === 0) {
            $serviceCenter = 'IMOWO 2 ';
        }

        if (strcasecmp($serviceCenter, 'ILORA') === 0) {
            $serviceCenter = 'ILORA';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'IJEBU IGBO') === 0) {
            $serviceCenter = 'IJEBU-IGBO';
        }

          // Normalize business hub value
        if (strcasecmp($serviceCenter, 'BODE-OLUDE') === 0) {
            $serviceCenter = 'BODE OLUDE';
        }


           // Normalize business hub value
        if (strcasecmp($serviceCenter, 'IDIROKO ROAD') === 0) {
            $serviceCenter = 'IDIROKO';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'OKE AGBO') === 0) {
            $serviceCenter = 'OKE-AGBO';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'OKE OWA') === 0) {
            $serviceCenter = 'OKE-OWA';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'OKE OWA') === 0) {
            $serviceCenter = 'OKE-OWA';
        }

         // Normalize business hub value
        if (strcasecmp($serviceCenter, 'Oke-soda') === 0) {
            $serviceCenter = 'oke-soda';
        }

        return ServiceAreaCode::whereRaw('LOWER(TRIM(Service_Centre)) = ?', [strtolower($serviceCenter)])
            ->whereRaw('LOWER(TRIM(BHUB)) = ?', [strtolower($businessHub)])
            ->where('number_of_customers', '<=', 1000)
            ->first();

    }


}
