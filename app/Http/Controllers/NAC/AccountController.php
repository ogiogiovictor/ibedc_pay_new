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


class AccountController extends BaseAPIController
{

    protected $apiKey = 'zMRaxKw9YTiTl5HD5sf9My0wg3s2vV2HeYip5wg0';
    protected $userId = '37';
    protected $baseUrl = 'https://fontanella.app/api/v1';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check if user with same name combo exists
        #$existingUser = AccoutCreaction::where('tracking_id', $request->tracking_id)->first();
         $existingUser = AccoutCreaction::with('continuation')->with('uploadinformation')->with('caccounts')->with('uploadedPictures')
        ->where('tracking_id', $request->tracking_id)
        ->first();

        if( $existingUser) {

             return $this->sendSuccess([
                    'customer' => $existingUser,
                ], 'CUSTOMER SUCCESSFULLY CREATED', Response::HTTP_OK);
        }

         return $this->sendError('No Customer Exists', 'ERROR', Response::HTTP_NOT_FOUND);

    }


    public function providedata(NinValidationRequest $request){


         $getAuth = (new NinService)->authenticate();

         if(isset($getAuth['token'])) {

            $data = $request->validated();
            $token =  $getAuth['token'];

            $response = Http::withHeaders([
            'X-API-KEY'    => $this->apiKey,
            'X-USER-ID'    => $this->userId,
            'Authorization' => "Bearer $token",
            'Accept'       => 'application/json',
            ])->post("{$this->baseUrl}/verifyNIN", [
                'nin' => $nin,
                'datasets' => [
                    "firstname", "middlename", "surname", "gender", "birth_date",
                    "birth_country", "birth_state", "birth_lga", "marital_status",
                    "religion", "title", "heigth", "educational_level", "emplyment_status",
                    "profession", "email", "telephone_no", "residence_adressline1", "residence_town",
                    "residence_lga", "residence_state", "residence_status", "nok_firstname",
                    "nok_middlename", "nok_surname", "nok_address1", "nok_address2", "nok_town",
                    "nok_lga", "nok_state", "nspokenlang", "ospokenlang", "pfirstname",
                    "pmiddlename", "psurname", "nin", "central_id", "tracking_id",
                    "card_status", "batch_id", "photo", "signature"
                ]
            ]);

            $fulldata =  $response->json();


           return $this->sendSuccess([
                    'customer' => $fulldata,
                ], 'CUSTOMER NIN DATA LOADED', Response::HTTP_OK);

         } else {

             return $this->sendError($getAuth, 'ERROR', Response::HTTP_UNAUTHORIZED);
         }



        

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountCreationRequest $request)  //AccountCreationRequest
    {
        $data = $request->validated();

        ////////////////////////////// IN ANY OTHER YOU EXISTS////////////////////////////////////
        // Normalize and collect all name parts
        $names = collect([
            strtolower(trim($data['surname'])),
            strtolower(trim($data['firstname'])),
        ]);

        if (!empty($data['other_name'])) {
            $names->push(strtolower(trim($data['other_name'])));
        }

        $sortedInputNames = $names->sort()->values()->toArray(); // e.g. ['john', 'michael', 'smith']

        // Search existing accounts where the sorted combination of names match
        $potentialMatches = AccoutCreaction::all()->filter(function ($user) use ($sortedInputNames) {
            $existingNames = collect([
                strtolower(trim($user->surname)),
                strtolower(trim($user->firstname)),
            ]);

            if (!empty($user->other_name)) {
                $existingNames->push(strtolower(trim($user->other_name)));
            }

            return $existingNames->sort()->values()->toArray() === $sortedInputNames;
        });

        if ($potentialMatches->isNotEmpty()) {
             return $this->sendError('A user with the same name (in any order) already exists. Please use your tracking ID to continue.', 'ERROR', Response::HTTP_UNAUTHORIZED);
         }




        $existingUserQuery = AccoutCreaction::where('surname', $data['surname'])
        ->where('firstname', $data['firstname']);

       if (array_key_exists('other_name', $data)) {
            if ($data['other_name'] === null) {
                $existingUserQuery->whereNull('other_name');
            } else {
                $existingUserQuery->where('other_name', $data['other_name']);
            }
        } else {
            $existingUserQuery->whereNull('other_name');
        }


        $existingUser = $existingUserQuery->first();

        if ($existingUser) {
            return $this->sendError('A user with the same name already exists. Please use your tracking ID to continue', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

        $checkEMS = ZoneCustomers::where('Surname', $data['surname'])->where('FirstName', $data['firstname'])->where('OtherNames', $data['other_name'])->first();

       

        if($checkEMS){
             $buid = BusinessUnit::where("BUID", $checkEMS->BUID)->first();  //Name
            //check the address if it is the same
            $locationExists = UploadHouses::where(['full_address' => $checkEMS->Address1, "business_hub" => $buid->Name])->first();
            
            if($locationExists) {
                return $this->sendError($checkEMS, 'ERROR - ACCOUNT NO ALREADY EXIST', Response::HTTP_UNAUTHORIZED);
            }
        }
        

        if(!$existingUser) {
            $requestData = $request->all();
            $requestData['status'] = 'started'; // or '1'
            $requestData['status_name'] = 'Application Initiated'; // or '1'
            $userData = AccoutCreaction::create($requestData);
             // You can customize the response based on your needs

             dispatch(new TrackingIDJob($userData));


            return $this->sendSuccess([
                    'customer' => $userData,
                ], 'CUSTOMER SUCCESSFULLY CREATED', Response::HTTP_OK);
        } 
            
        return $this->sendError('There was an error creating your account .', 'ERROR', Response::HTTP_UNAUTHORIZED);
        
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function continue(ContinueCustomerRequest $request) {


        if(!$request->tracking_id) {
              return $this->sendError('Please provide your tracking number to cotinue', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

         // Check if tracking ID exists
        $existingUser = AccoutCreaction::where('tracking_id', $request->tracking_id)->first();
        if(!$existingUser) {
             return $this->sendError('Invalid Tracking ID', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

        $data = $request->validated();

         $names = collect([
            strtolower(trim($data['landlord_surname'])),
            strtolower(trim($data['landlord_othernames'])),
        ]);

        $sortedInputNames = $names->sort()->values()->toArray(); // e.g. ['john', 'michael', 'smith']

         // Search existing accounts where the sorted combination of names match
        $potentialMatches = AccoutCreaction::all()->filter(function ($user) use ($sortedInputNames) {
            $existingNames = collect([
                strtolower(trim($user->landlord_surname)),
                strtolower(trim($user->landlord_othernames)),
            ]);

            return $existingNames->sort()->values()->toArray() === $sortedInputNames;
        });

        if ($potentialMatches->isNotEmpty()) {
             return $this->sendError('A user with the same name (in any order) already exists. Please use your tracking ID to continue.', 'ERROR', Response::HTTP_UNAUTHORIZED);
         }




        //Before you create check if the tracking ID already exist in the continue application model |  // Check if already continued
         $continueCustomer = ContinueAccountCreation::where('tracking_id', $request->tracking_id)->first();
         if($continueCustomer){

            return $this->sendSuccess([
                    'customer' => $continueCustomer,
                ], 'CUSTOMER SUCCESSFULLY CREATED', Response::HTTP_OK);
         }

          // Prepare data
        $data = $request->except(['landloard_picture']); // Exclude picture from mass assignment
        $data['customer_id'] = $existingUser->id;

        // Handle picture upload if present
        if ($request->hasFile('landloard_picture')) {
            //$folder = 'customers/pictures';

            $folder = "/customers/pictures";

            // Check and create the folder if it doesn't exist
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder, 0755, true); // recursive = true
            }

            $picturePath = $request->file('landloard_picture')->store($folder, 'public');
            $data['landloard_picture'] = $picturePath;
        }

           // Create the continue customer record
        $createdCustomer = ContinueAccountCreation::create($data); 

        // Update account creation status
        $existingUser->update([
            'status_name' => 'Application Advanced',
            'no_of_account_apply_for' => $request->no_of_account_apply_for,
        ]);

      

         if($createdCustomer) {
            return $this->sendSuccess([ 'customer' => $data, ], 'CUSTOMER SUCCESSFULLY CREATED', Response::HTTP_OK);
         }

          return $this->sendError('There was an error creating your account .', 'ERROR', Response::HTTP_UNAUTHORIZED);

    }


    public function upload(UploadRequest $request) {

        $data = $request->validated();
        $existingUser = AccoutCreaction::where('tracking_id', $request->tracking_id)->first();
         
        if (!$existingUser) {
                return $this->sendError('Invalid Tracking ID', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }


         if ($request->hasFile('identification') && $request->file('identification')->isValid()) {
            //$folder = 'customers/pictures';

            $folder = "/customers/pictures";

            // Check and create the folder if it doesn't exist
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder, 0755, true); // recursive = true
            }

            $picturePath = $request->file('identification')->store($folder, 'public');
            $data['identification'] = $picturePath;
        }


         if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            //$folder = 'customers/pictures';

            $folder = "/customers/pictures";

            // Check and create the folder if it doesn't exist
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder, 0755, true); // recursive = true
            }

            $picturePath = $request->file('photo')->store($folder, 'public');
            $data['photo'] = $picturePath;
        }


        $data['customer_id'] = $existingUser->id;

            $existingUser->update([
                'no_of_account_apply_for' => $request->no_of_account_apply_for,
            ]);

         // Check if the record exists and update or create
        $existingUpload = UploadAccountCreation::where('tracking_id', $request->tracking_id)->first();

        if ($existingUpload) {
        $existingUpload->update($data);
        return $this->sendSuccess(['customer' => $existingUpload], 'CUSTOMER SUCCESSFULLY UPDATED', Response::HTTP_OK);
        } else {
            $createdCustomer = UploadAccountCreation::create($data);
           
             if ($createdCustomer) {
                    return $this->sendSuccess(['customer' => $createdCustomer], 'CUSTOMER SUCCESSFULLY CREATED', Response::HTTP_OK);
                } else {
                    return $this->sendError('There was an error creating your account.', 'ERROR', Response::HTTP_UNAUTHORIZED);
                }


        }

      //  return $this->sendError('There was an error creating your account.', 'ERROR', Response::HTTP_UNAUTHORIZED);


    }



    public function final(Request $request) {

        $checkID =  $this->checktracking($request->tracking_id);

       // 🛑 If checktracking() returned an error response, return early
        if ($checkID instanceof \Illuminate\Http\JsonResponse) {
            return $checkID;
        }

        $request->validate([
            'tracking_id' => 'required|string',
            //'uploads' => 'required|array',
           // 'uploads.*.picture' => 'required|image|max:5120',
            //'uploads.*.latitude' => 'required|string',
            'uploads.*.type_of_premise' => 'required|string',
            'uploads.*.landmark' => 'required|string',
            'uploads.*.business_hub' => 'required|string',
            'uploads.*.service_center' => 'required|string',
            'uploads.*.lga' => 'required|string',
            'uploads.*.state' => 'required|string',
            'uploads.*.house_no' => 'required|string',
            'uploads.*.full_address' => 'required|string|min:10|max:255|regex:/^[a-zA-Z0-9\s,.\-\/]+$/',
           // 'uploads.*.full_address' => 'required|string',  
        ]);

        $folder = 'customers/pictures';

        // Create folder if it doesn't exist
        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder, 0755, true);
        }       

        // you need to check if the tracking no exist() oin UploadHouse
         // ✅ Check if uploads for this tracking_id already exist
        $existingUploads = UploadHouses::where(function ($query) use ($request) {
                $query->where('tracking_id', $request->tracking_id)
                    ->whereIn('status', [1, 2]);
            })->exists();

        if ($existingUploads) {
            return $this->sendError('Uploads for this tracking ID already exist.', 'DUPLICATE ENTRY', Response::HTTP_CONFLICT);
        }

        // ✅ Validate that none of the uploads already exist (house_no + full_address + lat/lng)
        foreach ($request->uploads as $upload) {
            $duplicate = UploadHouses::where('house_no', $upload['house_no'])
                ->where('full_address', $upload['full_address'])
                // ->where('latitude', $upload['latitude'])
                // ->where('longitude', $upload['longitude'])
                ->exists();

            if ($duplicate) {
                return $this->sendError(
                    "Duplicate record detected for House No: {$upload['house_no']}, Address: {$upload['full_address']}",
                    'ERROR',
                    Response::HTTP_CONFLICT
                );
            }
        }

      
        foreach ($request->uploads as $upload) {

           // $path = $upload['picture']->store($folder, 'public');

            UploadHouses::create([
                'customer_id' => $checkID->id,
                'tracking_id' => $request->tracking_id,
                'business_hub' => $upload['business_hub'], 
                'service_center' => $upload['service_center'], 
                'house_no' => $upload['house_no'], 
                'full_address' => $upload['full_address'],
                'nearest_bustop' => $upload['nearest_bustop'],
                'lga' => $upload['lga'],
                'picture' => 0,
                'latitude' => 0,
                'longitude' => 0,
                'landmark' => $upload['landmark'],
                'type_of_premise' => $upload['type_of_premise'],
                'use_of_premise' => $upload['use_of_premise'],
                'state' => $upload['state'],
            ]);
        }

         $checkID->update([
            'status' => 'processing',
            'status_name' => 'Pending Lecan Upload',
        ]);

        //Send email to the dtm and dte in that business hub to treat request

        //Return the LECAN LINK

        return $this->sendSuccess([ 'customer' => $checkID, 'lecan' => "You are required to complete the the form below with a registered
        electrician/Lecan engineer, Please click on the link below to download the form and return to the app to upload same with your tracking ID",
        'link' => 'https://ibedc.com/LECAN_FORM_IBEDC.pdf' ], 'CUSTOMER APPLICATION SUCCESSFUL SUBMITTED', Response::HTTP_OK);
    }



    public function lecanapplication(Request $request) {

         $checkID =  $this->checktracking($request->tracking_id);

         $request->validate([
            'tracking_id' => 'required|string',
            'uploads' => 'required|array',
           // 'uploads.*.lecan_link' => 'required|mimes:pdf'
            'uploads.*.lecan_link' => 'required|mimetypes:application/pdf',
            'uploads.*.id' => 'required|integer',
            //'uploads.*.lecan_link' => 'required|image|max:5120'
        ]);

        //$existingUploads = UploadHouses::whereIn('id', $request->id)->get();
        $uploadIds = collect($request->uploads)->pluck('id')->all();

        $existingUploads = UploadHouses::whereIn('id', $uploadIds)->get();

        // Check count consistency
        if (count($uploadIds) !== count($request->uploads)) {
            return response()->json(['error' => 'Mismatch between IDs and uploads.'], 422);
        }

        $folder = 'customers/pictures';

        // Create folder if it doesn't exist
        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder, 0755, true);
        }       


         // Handle PDF uploads and update existing records
        foreach ($request->uploads as $index => $upload) {
            //$uploadRecord = $existingUploads->firstWhere('id', $request->id[$index]);
            $uploadRecord = $existingUploads->firstWhere('id', $upload['id']);

            if ($uploadRecord && isset($upload['lecan_link'])) {
                //$path = $upload['lecan_link']->store('lecan_uploads', 'public');
                $path = $upload['lecan_link']->store($folder, 'public');

                $uploadRecord->update([
                    'lecan_link' =>  $path,
                    'status' => 1,
                ]);
            }
        }

        //we should have a job that send email to the DTM
        dispatch(new AccountNotificationJob($request->tracking_id));

        $checkID->update([
            'status' => 'with-dtm',
            'status_name' => 'Lecan Successfully Uploaded',
        ]);

        return $this->sendSuccess([ 'customer' => $existingUploads ], 'CUSTOMER APPLICATION SUCCESSFUL SUBMITTED', Response::HTTP_OK);
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

    public function region(){

       // $get_regions = Regions::select("Region")->distinct()->get();
         $get_regions = Regions::select("Region")
        ->distinct()
        ->get()
        ->map(function ($item) {
            // Remove the word "REGION" and trim whitespace
            return ['Region' => trim(str_ireplace('REGION', '', $item->Region))];
        });
          
        return $this->sendSuccess([ 'regions' => $get_regions, ], 'All Regions Loaded', Response::HTTP_OK);

    }

     public function businesshub($region_name){

        $region_name  = $region_name. " REGION";
        $get_business_hubs = Regions::where("Region", $region_name)->get();
          
        return $this->sendSuccess([ 'business_hubs' => $get_business_hubs ], 'All Business Hubs', Response::HTTP_OK);

    }

     public function allBusinessHub(){

        $get_business_hubs = Regions::get();
          
        return $this->sendSuccess([ 'business_hubs' => $get_business_hubs ], 'All Business Hubs', Response::HTTP_OK);

    }



    


     public function getDss(Request $request){

         $region = $request->query('region');
         $hub = $request->query('hub');
         $servicecenter = $request->query('service_center');
   

        $region =  $region == "IBADAN" ? "OYO" : $region;

        $get_dss = DSS::select("Assetid", "assettype", "DSS_11KV_415V_Owner", "DSS_11KV_415V_Name", "DSS_11KV_415V_Address", 
        "hub_name", "Status", "Feeder_Name", "Feeder_ID", "BAND")->where(["Dss_State" =>$region,   "hub_name" => $hub, "DSS_11KV_415V_Owner" => $servicecenter ])->get();
          
        return $this->sendSuccess([ 'dss' => $get_dss ], 'DSS Loaded', Response::HTTP_OK);

    }

    public function getDssServiceCenter($business_hub_name){

        $get_service = DSS::select("DSS_11KV_415V_Owner")->where("hub_name", $business_hub_name)->distinct()->get();
          
        return $this->sendSuccess([ 'service_center' => $get_service ], 'Service Center Loaded', Response::HTTP_OK);

    }


    public function getTarriff(Request $request){

        $tarriff = NewTarrif::get();
         return $this->sendSuccess([ 'tarriff' => $tarriff ], 'Tarriff Loaded', Response::HTTP_OK);

    }


    public function dtmprocess(Request $request){

         $checkID =  $this->checktracking($request->tracking_id);

       // 🛑 If checktracking() returned an error response, return early
        if ($checkID instanceof \Illuminate\Http\JsonResponse) {
            return $checkID;
        }

        $request->validate([
            'id' => 'required|string',
            'tracking_id' => 'required|string',
            'picture' => 'required|image|max:5120',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
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

       // Handle picture upload if present
        if ($request->hasFile('picture')) {
            //$folder = 'customers/pictures';

            $folder = "/customers/pictures";

            // Check and create the folder if it doesn't exist
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder, 0755, true); // recursive = true
            }

            $picturePath = $request->file('picture')->store($folder, 'public');
            $request['picture'] = $picturePath;
        }


        $checkUpdate = UploadHouses::where("id", $request->id)->update([
            'picture' => $picturePath,
            'latitude' => $request['latitude'],
            'longitude' => $request['longitude'], 
            'region' => $request['region'],
            'business_hub' => $request['business_hub'], 
            'service_center' => $request['service_center'], 
            'dss' => $request['dss'], 
            'tarrif' => $request['tarrif'], 
            'status' => isset(Auth::user()->id) ? 2 : 1,
            'validated_by' => isset(Auth::user()->id) ? Auth::user()->email : $request->email,  // use the code to validate the email
            'comment' => $request->email
        ]);
        

        $update = AccoutCreaction::where('id',$checkID->id)->update([
             'status' => 'with-billing',
            'status_name' => 'Account Verified by DTM',
        ]);

        return $this->sendSuccess([ 'customer' => $checkID, 'lecan' => "You are required to complete the the form below with a registered
        electrician/Lecan engineer, Please click on the link below to download the form and return to the app to upload same with your tracking ID",
        'link' => 'https://ibedc.com/LECAN_FORM_IBEDC.pdf' ], 'CUSTOMER APPLICATION SUCCESSFUL SUBMITTED', Response::HTTP_OK);


    }


    public function changedtmprocess(Request $request) {

        
         $checkID =  $this->checktracking($request->tracking_id);

       // 🛑 If checktracking() returned an error response, return early
        if ($checkID instanceof \Illuminate\Http\JsonResponse) {
            return $checkID;
        }

        $request->validate([
            'id' => 'required|string',
            'tracking_id' => 'required|string',
            'region' => 'string',
            'business_hub' => 'string',
            'service_center' => 'string'
        ]);  



        $checkUpdate = UploadHouses::where("id", $request->id)->update([
            'region' => $request['region'],
            'business_hub' => $request['business_hub'], 
            'service_center' => $request['service_center'], 
            'validated_by' => isset(Auth::user()->id) ? Auth::user()->email : $request->email  // use the code to validate the email
        ]);
        


        return $this->sendSuccess([ 'customer' => $checkID, 'chang4e' => "Your update haven been set"], Response::HTTP_OK);


    }


    

    public function getpendingaccounts() {
        //Auth
        $user = Auth::user();

        $data = UploadHouses::where("business_hub", $user->business_hub)->whereIn("status", ["0", "1"])->with('account')->paginate(10);

        return $this->sendSuccess([ 'accounts' => $data], 'CUSTOMER APPLICATION SUCCESSFUL SUBMITTED', Response::HTTP_OK);
    }


    public function reject(Request $request) {

         $request->validate([
            'id' => 'required|string'
        ]);  


         $user = Auth::user();

        $updated = UploadHouses::where('id', $request->id)->update([
            'lecan_link' => 0,
            'status' => 0,
        ]);

        return $this->sendSuccess(
            ['accounts' => $updated],
            'CUSTOMER APPLICATION SUCCESSFULLY REJECTED',
            Response::HTTP_OK
        );

    }


    public function approveDTERequest(Request $request){
        
         $request->validate([
            'id' => 'required|string',
            'tracking_id' => 'required|string',
            'comment' => 'required',
            'type' => 'required'
        ]);  

        if($request->type == 'approve'){

             $checkUpdate = UploadHouses::where("id", $request->id)->update([
            'status' => isset(Auth::user()->id) ? 2 : 1,
            'validated_by' => isset(Auth::user()->id) ? Auth::user()->email : $request->email,  // use the code to validate the email
            'comment' => $request->comment
        ]);

        } else {

             $checkUpdate = UploadHouses::where("id", $request->id)->update([
                'status' => 1,
                'comment' => $request->comment
             ]);

        }
        

        return $this->sendSuccess([ 'accounts' => $request->all()], 'CUSTOMER APPLICATION SUCCESSFUL UPDATED', Response::HTTP_OK);

    }


    public function NINService(Request $request){


        $getAuth = (new NinService)->authenticate();

        return $getAuth;
        //return $getAuth['token'];
    }

    
    



}
