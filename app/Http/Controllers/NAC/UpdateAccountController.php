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
use App\Http\Requests\NinValidationRequest;
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
use App\Jobs\AccountNotificationJob;
use App\Services\NinService;
use Illuminate\Support\Facades\Http;
use App\Models\NINDetails;

use App\Models\EMS\Undertaking;
use App\Models\NAC\ServiceAreaCode;
use App\Jobs\CustomerAccountJob;
use App\Jobs\CustomerJobFeedback;

use Illuminate\Support\Facades\Mail;
use App\Jobs\IncreaseCustomerAccountJob;
use App\Services\IbedcPayLogService;
use App\Models\User;

use App\Models\NAC\MeterInstallation;

use Illuminate\Support\Facades\Log;
use App\Models\MSMSPayload;

class UpdateAccountController extends BaseAPIController
{
    public function customeruploadlatlong(Request $request)
    {
       
        $checkID =  $this->checktracking($request->tracking_id);

       // 🛑 If checktracking() returned an error response, return early
        if ($checkID instanceof \Illuminate\Http\JsonResponse) {
            return $checkID;
        }


        $statusCount = UploadHouses::where('tracking_id', $request->tracking_id)
        ->where('status', 4)
        ->count();

       

        $numberOfaccount = AccoutCreaction::where('tracking_id', $request->tracking_id)->first();

        if ($statusCount > $numberOfaccount->default_house_no) {
            return $this->sendError(
                    'The number of accounts  for this tracking ID exceeds the allowed limit ' . $numberOfaccount->default_house_no . '. You cannot approve request, contact administrator',
                    'LIMIT EXCEEDED',
                    Response::HTTP_FORBIDDEN
                );
        }

     

        $request->validate([
            'tracking_id' => 'required|string',
            'uploads' => 'required|array',
            'uploads.*.picture' => 'required|mimes:jpeg,jpg,pdf,png,heic|max:10240',
            'uploads.*.latitude' => 'required|string',
            'uploads.*.longitude' => 'required|string',
            'uploads.*.id' => 'required|integer|exists:upload_houses,id'
        ]);
        
        
         // Check for duplicate location (lat + long)
            foreach ($request->uploads as $upload) {
                $locationExists = UploadHouses::where('latitude', $upload['latitude'])
                    ->where('longitude', $upload['longitude'])
                    ->where('id', '!=', $upload['id']) // ignore current row
                    ->exists();

                if ($locationExists) {
                    return $this->sendError(
                        'Duplicate location found. Ensure each building/flat upload is done at its physical location.',
                        'DUPLICATE LOCATION',
                        422
                    );
                }
            }


        //$existingUploads = UploadHouses::whereIn('id', $request->id)->get();
        $uploadIds = collect($request->uploads)->pluck('id')->all();

        $existingUploads = UploadHouses::whereIn('id', $uploadIds)->get();

       // return $existingUploads;

        // Check count consistency
        if (count($uploadIds) !== count($request->uploads)) {
            return response()->json(['error' => 'Mismatch between IDs and uploads.'], 422);
        }

        //  if ($existingUploads->count() !== count($uploadIds)) {
        //     return $this->sendError('One or more upload IDs are invalid or missing.', 'INVALID IDS', 422);
        // }


        $folder = 'customers/pictures';

        // Create folder if it doesn't exist
        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder, 0755, true);
        }    
        
        $uploadedPictures = [];
         // Handle PDF uploads and update existing records
        foreach ($request->uploads as $index => $upload) {
            //$uploadRecord = $existingUploads->firstWhere('id', $request->id[$index]);
            $uploadRecord = $existingUploads->firstWhere('id', $upload['id']);

            if ($uploadRecord && isset($upload['picture'])) {
                //$path = $upload['lecan_link']->store('lecan_uploads', 'public');
                $path = $upload['picture']->store($folder, 'public');

                // if($path == false) {
                //     return $this->sendError('Failed to upload picture for ID: ' . $upload['id'], 'UPLOAD ERROR', Response::HTTP_INTERNAL_SERVER_ERROR);
                // }

                $uploadRecord->update([
                  'picture' =>  $path,
                  'latitude'  => $upload['latitude'],
                  'longitude' => $upload['longitude'],
                   'status' => 1

                ]);

                 // collect response data
                $uploadedPictures[] = [
                    'id' => $uploadRecord->id,
                    'picture' => $path,
                    'url' => Storage::url($path),
                ];
            }
        }


         //we should have a job that send email to the DTM
        dispatch(new AccountNotificationJob($request->tracking_id));

         $checkID->update([
            'status' => 'with-dtm',
            'status_name' => 'Lecan Successfully Uploaded',
        ]);


         IbedcPayLogService::create([
                    'module'     => 'Customer House Upload LatLong',
                    'comment'    => '',
                    'type'       => 'Approved',
                    'module_id'  => $uploadIds[0] ?? 0,  //json_encode($uploadIds),  // Tracks all updated houses
                    'status'     => '', 
                    'user_email' => isset(Auth::user()->email) ? Auth::user()->email : 'System',
            ]);



        return $this->sendSuccess([ 'customer' => $checkID, 'uploads'  => $uploadedPictures,  'picture' => "Latitude and Longitude Uploaded Successfully"], 'CUSTOMER APPLICATION SUCCESSFUL SUBMITTED', Response::HTTP_OK);

    }




    public function dtmprocess(Request $request){

        $checkID =  $this->checktracking($request->tracking_id);

       // 🛑 If checktracking() returned an error response, return early
        if ($checkID instanceof \Illuminate\Http\JsonResponse) {
            return $checkID;
        }


        $statusCount = UploadHouses::where('tracking_id', $request->tracking_id)
        ->where('status', 4)
        ->count();

        $numberOfaccount = AccoutCreaction::where('tracking_id', $request->tracking_id)->first();

        if ($statusCount > $numberOfaccount->default_house_no) {
            return $this->sendError(
                    'The number of accounts  for this tracking ID exceeds the allowed limit (10). You cannot approve request, contact administrator',
                    'LIMIT EXCEEDED',
                    Response::HTTP_FORBIDDEN
                );
        }

        $request->validate([
            'id' => 'required|string',
            'tracking_id' => 'required|string',
            'region' => 'required|string',
            'business_hub' => 'required|string',
            'service_center' => 'required|string',
            'dss' => 'required|string', 
            'tarrif' => 'required|string', 
        ]);  

        
        // ✅ Check if latitude + longitude already exist
        $locationExists = UploadHouses::where('latitude', $request['latitude'])->where('longitude', $request['longitude'])->exists();
        if ($locationExists) {
            return $this->sendError(
                'One or more uploads contain a duplicate location (latitude + longitude already exists). Please move to each building/flat when uploading..',
                'ERROR',
                //Response::HTTP_CONFLICT
                422
            );
        }


        $checkUpdate = UploadHouses::where("id", $request->id)->update([
            'region' => $request['region'],
            'business_hub' => $request['business_hub'], 
            'service_center' => $request['service_center'], 
            'dss' => $request['dss'], 
            'tarrif' => $request['tarrif'], 
            'status' => 2, 
            //'evaluated' => "yes",
            'validated_by' => isset(Auth::user()->id) ? Auth::user()->email : $request->email,  // use the code to validate the email
        ]);

        
        if(Auth::check()) {
            IbedcPayLogService::create([
                'module'     => 'DTM',
                'comment'    => '',
                'type'       => 'Approved',
                'module_id'  => $request->id,
                'status'     => 'with-billing',  //'with-compliance',
                'user_email' => isset(Auth::user()->email) ? Auth::user()->email : 'System',
            ]);
        }
        
        $update = AccoutCreaction::where('id', $checkID->id)->update([
            'status' => Auth::check() ? 'with-billing' : 'with-dtm',    //Auth::check() ? 'with-compliance' : 'with-dtm',
            'status_name' => 'Account Verified by ' . (Auth::check() ? Auth::user()->email : $request->email),
            'region' => $request->input('region'),
        ]);


        $uploadHouses = UploadHouses::where("id", $request->id)->first();
       // $this->generateAccount($request->id, $uploadHouses);  uncomment later   evaluated = yes

        $get_region = User::where('region', $request['region'])->where('authority', 'region')->first();
       //Send Notification to Regional Regional Manager

       
        if ($get_region && $get_region->email) {

            $emailData = [
                'tracking_id'    => $request->tracking_id,
                'region'         => $request->region,
                'business_hub'   => $request->business_hub,
                'service_center' => $request->service_center,
                'dss'            => $request->dss,
                'tarrif'         => $request->tarrif,
                'validated_by'   => Auth::check() ? Auth::user()->email : 'DTM System',
            ];
            
            // Mail::send([], [], function ($message) use ($get_region, $emailData) {
            //     $message->to($get_region->email)
            //         ->cc($emailData['validated_by'])
            //         ->bcc([
            //             'victor.ogiogio@ibedc.com',
            //             'babatunde.bodunde@ibedc.com',
            //         ])
            //         ->subject('DTM Validation Completed – Account Forwarded for Approval')
            //         ->html("
            //             Dear {$get_region->name},<br><br>

            //             This is to formally notify you that a (DTM) validation has been successfully completed for a customer account under your region.<br><br>

            //             <strong>Validation Summary:</strong><br>
            //             Tracking ID: {$emailData['tracking_id']}<br>
            //             Region: {$emailData['region']}<br>
            //             Business Hub: {$emailData['business_hub']}<br>
            //             Service Center: {$emailData['service_center']}<br>
            //             DSS: {$emailData['dss']}<br>
            //             Tariff: {$emailData['tarrif']}<br>
            //             Validation Status: Approved and forwarded for your approval<br><br>

            //             You can log in to the mobile app or web (https://pay.ibedc.com/) to approve the request.<br><br>

            //             Alternatively, you can use https://ipay.ibedc.com:7642/ to approve the request.<br><br>

            //             Warm regards,<br>
            //             {$emailData['validated_by']}<br>
            //             DTM<br>
            //             IBEDC
            //         ");
            // });


        }

        if($uploadHouses->paid_for_meter == "No") {
           $syncResponse = $this->synctoMSMS($request->id);
        }

        if($uploadHouses->paid_for_meter == "Old") {
           $syncResponse = $this->synctoMSMS($request->id);
        }
        

        return $this->sendSuccess([ 'customer' => $checkID, ], 'DTM VALIDATION SUCCESSFUL', Response::HTTP_OK);


    }




    
    private function checktracking($trackingid){

        if(!$trackingid) {
              return $this->sendError('Please provide your tracking number to cotinue', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

         // Check if tracking ID exists
        $existingUser = AccoutCreaction::where('tracking_id',$trackingid)->first();
        if(!$existingUser) {
             return $this->sendError('Invalid Tracking ID', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

        return $existingUser;

    }



    private function synctoMSMS($id) {

    // API Key
    $apiKey = "LIVEKEY_0XJLDYJZOQWF8UQ9XWVTH";

    // 1. Get approved house record
    $data = UploadHouses::with(['landlordinfo', 'account'])
        ->where("status", "2")
        ->findOrFail($id);

    //return $data->id;

    // 2. Get MSMS Locations
    $locationResponse = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Accept' => 'application/json'
    ])->get('https://msms.ibedc.com/api/v2/setup/locations');

    // Check if request failed
    if (!$locationResponse->successful()) {
        return [
            "success" => false,
            "message" => "Unable to fetch MSMS locations",
            "payload" => $locationResponse->json()
        ];
    }

    $locations = $locationResponse->json();

    if (!isset($locations['data'])) {
        return [
            "success" => false,
            "message" => "Invalid response from locations API",
            "payload" => $locations
        ];
    }


    $regions = $locations['data']['Regions'];
    $bhubs = $locations['data']['BusinessHubs'];

     /**
     * BUSINESS HUB MAPPING + NORMALIZATION
     */
    $bhubMapping = [
        'Mowe-ibafo' => 'Mowe-Ibafo',
        'ilesa' => 'Ilesha',
        'ile-ife' => 'Ileife',
    ];

    $normalize = function ($value) {
        return strtolower(str_replace(['-', ' '], '', $value));
    };

    // Normalize incoming value
    $normalizedInput = $normalize($data->business_hub);

    // Apply mapping if exists
    if (isset($bhubMapping[$normalizedInput])) {
        $businessHubName = $bhubMapping[$normalizedInput];
    } else {
        $businessHubName = $data->business_hub;
    }

   

    // 3. Match Region ID
    $region = collect($regions)->firstWhere('name', $data->region . ' Region');
    $region_id = $region['id'] ?? null;


    // 4. Match Business Hub ID
   // $bhub = collect($bhubs)->firstWhere('name', $data->business_hub);
     /**
     * 🔹 BUSINESS HUB MATCH (WITH FALLBACK)
     */
    $bhub = collect($bhubs)->firstWhere('name', $businessHubName);

    // fallback (normalized match)
    if (!$bhub) {
        $bhub = collect($bhubs)->first(function ($item) use ($normalizedInput, $normalize) {
            return $normalize($item['name']) === $normalizedInput;
        });
    }

    $bhub_id = $bhub['id'] ?? null;

    // 5. Prepare payload for MSMS create API
    $payload = [
        "tracking_id" => $data->id,
        // "tracking_id" => $data->id."-".$data->tracking_id,
        "cust_name" => trim(
            $data->landlordinfo->landlord_surname . ' ' .
            $data->landlordinfo->landlord_othernames
        ),
        "address" => $data->full_address,
        "lga" => $data->lga,
        "city" => $data->business_hub,
        "state" => $data->state,
        "premises_use" => $data->use_of_premise,
        "phone_no" =>  $data->landlordinfo->landlord_telephone ?? $data->account->phone,
        "email" => $data->landlordinfo->landlord_email ?? $data->account->email,
        "region_id" => $region_id,
        "bhub_id" => $bhub_id,
        "service_center" => $data->service_center,
        "dss_id" => $data->dss,
        "id_type" => $data->landlordinfo->landlord_personal_identification,
        "id_no" => $data->landlordinfo->nin_number,
        "contact_name" => trim(
            $data->account->surname . ' ' .
            $data->account->firstname . ' ' .
            $data->account->other_name
        ),
        "contact_phone" => $data->account->phone ?? $data->landlordinfo->landlord_telephone,
        "contact_email" => $data->account->email ?? $data->landlordinfo->landlord_email
    ];

   // return $payload;

   if($data->map_id == null) {
       
        // 6. Send to MSMS create API
        $createResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json'
        ])->post(
            'https://msms.ibedc.com/api/v2/setup/create',
            $payload
        );

    }
   

    //Send email to customer informaing the customer gto go and pay for meter before their account can be generated. add the link to the msms portal for payment

     $customerEmail = $data->account->email;
     $customerName = $data->account->surname . ' ' . $data->account->firstname;
     $landlordEmail = $data->landlordinfo->landlord_email;

     $responseData = $createResponse->json();

    // Safely get MAP ID (note the space in key)
    $mapId = $responseData['data']['MAP ID'] ?? null;

    //  Mail::raw("Dear {$customerName},\n\nYour meter processing with id {$data->id} and tracking id {$data->tracking_id} has been successfully validated. Please proceed to the MSMS portal to complete your payment and finalize your account setup.\n\nMSMS Portal: https://msms.ibedc.com/\n\nThank you for choosing IBEDC.", function ($message) use ($customerEmail, $customerName) {
    //      $message->to($customerEmail)
    //              ->subject('Meter Process Validated - Next Steps');
    //  });

    if ($mapId) {
            UploadHouses::where('id', $id)
                // ->whereNull('map_id')
                ->update([
                    'map_id' => $mapId
                ]);



       // put a try catch block around the mail function to catch any errors that may occur during the email sending process and log them for further investigation. this is important because we don't want the entire process to fail just because of an email sending issue. we also want to make sure that the customer is informed about the status of their meter processing even if the email fails to send.
        try {
            Mail::raw("Dear {$customerName},\n\nYour meter processing with tracking ID {$data->tracking_id} has been successfully validated.\n\nYour MAP ID is: {$mapId}\n\nPlease proceed to the MSMS portal to complete your payment and finalize your account setup.\n\nMSMS Portal: https://msms.ibedc.com/\n\nThank you for choosing IBEDC.", function ($message) use ($customerEmail, $customerName, $landlordEmail) {
                $message->to($customerEmail)
                        ->cc($landlordEmail)
                       // ->bcc("customercare@ibedc.com") 
                        ->subject('Meter Process Validated - Next Steps');
             });
        } catch (\Exception $exception) {
            Log::error('Failed to send MSMS validation email', [
                'tracking_id' => $data->tracking_id,
                'customer_email' => $customerEmail,
                'landlord_email' => $landlordEmail,
                'error' => $exception->getMessage(),
            ]);
        }

        return [
            "success" => $createResponse->successful(),
            "message" => "MSMS API Response",
            "payload" => $createResponse->json()
        ];


    }

    if (!$createResponse->successful() || !$mapId) {

            // Store payload safely
            MSMSPayload::create([
                'customer_id' => $data->id,
                'tracking_id' => $data->tracking_id,
                'content' => json_encode($payload, JSON_PRETTY_PRINT)
            ]);

            // Convert payload to readable format for email
            $payloadText = json_encode($payload, JSON_PRETTY_PRINT);

            // Send alert email
            Mail::raw(
                "Dear Team,\n\n" .
                "Meter processing FAILED for the following details:\n\n" .
                "Tracking ID: {$data->tracking_id}\n" .
                "Customer Name: {$customerName}\n\n" .
                "Payload Sent:\n{$payloadText}\n\n" .
                "Response:\n" . json_encode($createResponse->json(), JSON_PRETTY_PRINT) . "\n\n" .
                "Please investigate immediately.\n\n" .
                "Regards,\nSystem",
                function ($message) {
                    $message->to("victor.ogiogio@ibedc.com")
                             ->cc("Tolulope.Olaniyan@ibedc.com")
                            ->subject('🚨 MSMS Sync Failed');
                }
            );

            // Optional: log error
            // \Log::error('MSMS Sync Failed', [
            //     'tracking_id' => $data->tracking_id,
            //     'payload' => $payload,
            //     'response' => $createResponse->json()
            // ]);

            return [
                "success" => false,
                "message" => "Failed to create MSMS record",
                "payload" => $createResponse->json()
            ];
    }

    

   

    }




    //Meter has been installed, this is the webhook that will be called by the field engineers to notify us that the meter has been installed and we can proceed to generate the account for the customer. we will also send an email to the customer informing them that their meter has been installed and they can now proceed to make payment for their meter before their account can be generated. we will also send an email to the DTM team to inform them that the meter has been installed and they can now proceed to generate the account for the customer.
    public function notifySetup(Request $request) {


        // Step 1: Validate incoming request
        $data = $request->validate([
            'meter_number' => 'required|string',
            'mapid' => 'required|string',
            'tracking_id' => 'required|string',
            'installed_at' => 'required|date',
            'installed_by' => 'required|string',
            'longitude' => 'required',
            'latitude' => 'required',
            'payment_status' => 'required',
            'amount_paid' => 'required',
            'payment_date' => 'nullable|date',
            'install_status' => 'required|string',
        ]);


        $meterInstallation = new MeterInstallation();
        $meterInstallation->meter_number = $data['meter_number'];
        $meterInstallation->mapid = $data['mapid'];
        $meterInstallation->tracking_id = $data['tracking_id'];
        $meterInstallation->installed_at = $data['installed_at'];
        $meterInstallation->installed_by = $data['installed_by'];
      //  $meterInstallation->logitude = $data['logitude'];
        $meterInstallation->longitude = $data['longitude'];
        $meterInstallation->latitude = $data['latitude'];
        $meterInstallation->payment_status = $data['payment_status'];
        $meterInstallation->amount_paid = $data['amount_paid'];
        $meterInstallation->payment_date = $data['payment_date'] ?? now();
        $meterInstallation->install_status = $data['install_status'];
        $meterInstallation->save();


        //Update the upload house table to set the metered status to yes and also update the account creation table to set the metered status to yes as well. we will use the tracking id to update the records in both tables.
        UploadHouses::where('id', $data['tracking_id'])->update(['paid_for_meter' => 'Yes']);

        // Step 2: Log the incoming request for auditing
        Log::info('Meter Installed Webhook received', $data);

        // Step 3: Send the log data via email
        $logContent = "Meter Installation Webhook Data:\n\n" . json_encode($data, JSON_PRETTY_PRINT);

        Mail::raw($logContent, function ($message) use ($data) {
            $message->to('victor.ogiogio@ibedc.com')
                    ->subject("Meter Installed Notification - {$data['meter_number']}");
        });

        // Step 4: Respond to the webhook sender
        return response()->json(['status' => 'success'], 200);

    }


}
