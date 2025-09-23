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

// 0 - customer, 1 = dtm 4 = completed 5 = rejected  2 = with billing

class AccountController extends BaseAPIController
{

    protected $apiKey = 'hz3czznmfOA17ArOK9Tt0MQCejzto2LhmLFMbDUC';
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


        $checkDB = NINDetails::where("nin", $request->nin)->first();

        if($checkDB) {
             return $this->sendSuccess([
                    'customer' => $checkDB,
                ], 'CUSTOMER NIN DATA LOADED', Response::HTTP_OK);
        } 

         $getAuth = (new NinService)->authenticate();


         if(isset($getAuth['token'])) {

            $data = $request->validated();
            $token =  $getAuth['token'];

            $response = Http::withHeaders([
            'X-API-KEY'    => $this->apiKey,
            'X-USER-ID'    => $this->userId,
            'Authorization' => "Bearer $token",
            'Accept'       => 'application/json',
            ])->post("{$this->baseUrl}/ninAuth/getNINDetails", [
                'nin' => $request->nin,
                'requestReason' => "taxationAssessment",
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

          //  return $fulldata;

          $create = NINDetails::create([
                'nin' => $request->nin,
                'payload' => $fulldata
          ]);

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


        //////////////////////////////////////////// --  EMS VALIDATION -- ////////////////////////////////
            // 🔎 Normalize request names (lowercase + trim)
            $requestNames = collect([
                strtolower(trim($data['surname'])),
                strtolower(trim($data['firstname'])),
            ])->sort()->values()->toArray();

            // 🔎 Fetch possible Zone matches (only surname/firstname columns)
             $zoneCustomers = ZoneCustomers::whereIn('Surname', $requestNames)
            ->orWhereIn('FirstName', $requestNames)
            ->get(['Surname', 'FirstName']);

              $exists = $zoneCustomers->contains(function ($zone) use ($requestNames) {
                $zoneNames = collect([
                    strtolower(trim($zone->Surname)),
                    strtolower(trim($zone->FirstName)),
                ])->sort()->values()->toArray();

                return $zoneNames === $requestNames; // Match in any order
            });

            if ($exists) {
                return $this->sendError(
                    'A user with the same surname and firstname already exists in our records. Please login with your tracking ID',
                    'ERROR - ACCOUNT NO ALREADY EXIST',
                    Response::HTTP_UNAUTHORIZED
                );
            }

             //////////////////////////////////////////// --  END OF EMS VALIDATION -- ////////////////////////////////
        

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
        $potentialMatches = ContinueAccountCreation::all()->filter(function ($user) use ($sortedInputNames) {
            $existingNames = collect([
                strtolower(trim($user->landlord_surname)),
                strtolower(trim($user->landlord_othernames)),
            ]);

            return $existingNames->sort()->values()->toArray() === $sortedInputNames;
        });

        if ($potentialMatches->isNotEmpty()) {
             return $this->sendError('A user with the same name (in any order) already exists. Please use your tracking ID to continue.', 'ERROR', Response::HTTP_UNAUTHORIZED);
         }


         $checkEMS = ZoneCustomers::where('Surname', $data['landlord_surname'])->where('FirstName', $data['landlord_othernames'])->first();

        if($checkEMS){
             $buid = BusinessUnit::where("BUID", $checkEMS->BUID)->first();

             if($buid) {
                return $this->sendError($buid, 'ERROR - ACCOUNT EXIST, VISIT OUR OFFICE FOR SUPPORT', Response::HTTP_UNAUTHORIZED);
            }

            //check the address if it is the same
            $locationExists = UploadHouses::where(['full_address' => $checkEMS->Address1, "business_hub" => $buid->Name])->first();
            
            if($locationExists) {
                return $this->sendError($checkEMS, 'ERROR - ACCOUNT NO ALREADY EXIST', Response::HTTP_UNAUTHORIZED);
            }
        }




        //Before you create check if the tracking ID already exist in the continue application model |  // Check if already continued
         $continueCustomer = ContinueAccountCreation::where('tracking_id', $request->tracking_id)->first();

         if($continueCustomer){

            return $this->sendSuccess([
                    'customer' => $continueCustomer,
                ], 'CUSTOMER SUCCESSFULLY CREATED', Response::HTTP_OK);
         }


         // Check for NIN
         $ninexist = ContinueAccountCreation::where('nin_number', $request->nin_number)->first();

         if($ninexist) {
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

         $startedCount = UploadHouses::where('tracking_id', $request->tracking_id)
        ->where('status', 0)
        ->count();

         if ($startedCount > 10) {    // default_house_no

            return $this->sendError(
                    'The number of accounts  for this tracking ID exceeds the allowed limit (15). Please visit our offices for more information',
                    'LIMIT EXCEEDED',
                    Response::HTTP_FORBIDDEN
                );
        }



        $statusCount = UploadHouses::where('tracking_id', $request->tracking_id)
        ->where('status', 4)
        ->count();

        $numberOfaccount = AccoutCreaction::where('tracking_id', $request->tracking_id)->first();
        $allAccounts = UploadHouses::where('tracking_id', $request->tracking_id)->first();

        if ($statusCount > $numberOfaccount->default_house_no) {    // default_house_no

            // Send email to the business manager and regional head with this customer information and summary copy cco
             dispatch(new IncreaseCustomerAccountJob($numberOfaccount, $allAccounts));

            // if account is more than 10 goto EMS look for the active accounts and check if it has payments. eg. 
            //if we are in the current month say march the customer must have made payment for the pastt 3 months or have no outstanding balance.
            // $getAccount = UploadHouses::where('tracking_id', $request->tracking_id)->get();
            // $paymentCheck = $this->checkAllaccountPayments($getAccount);
            // if ($paymentCheck instanceof \Illuminate\Http\JsonResponse) {
            //     return $paymentCheck; // 🚨 Stop if accounts fail
            // }
            return $this->sendError(
                    'The number of accounts  for this tracking ID exceeds the allowed limit (10). Please visit our offices for more information',
                    'LIMIT EXCEEDED',
                    Response::HTTP_FORBIDDEN
                );
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
           // 'uploads.*.house_no' => 'required|string',
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
                 ->where('business_hub', $upload['business_hub'])
                 //->where('service_center', $upload['service_center'])
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
            'status_name' => 'Pending Form Upload',
        ]);

        //Send email to the dtm and dte in that business hub to treat request and send the customer a link to download the form.

        //Return the LECAN LINK

        return $this->sendSuccess([ 'customer' => $checkID, 'lecan' => "You are required to complete the the form below with a registered
        electrician/licence engineer, Please click on the link below to download the form and return to the app to upload same with your tracking ID",
        'link' => 'https://ibedc.com/LECAN_FORM_IBEDC.pdf' ], 'CUSTOMER APPLICATION SUCCESSFUL SUBMITTED', Response::HTTP_OK);
    }



    private function checkAllaccountPayments(array $accounts) {

           $accountNumbers = $accounts->pluck('account_no')->toArray();

            if (count($accountNumbers) <= 10) {
                return true; // ✅ No need to check EMS
            }

            $failedAccounts = [];


             foreach ($accountNumbers as $accountNumber) {
                // 🔹 Query EMS (replace with your actual EMS service call)
                $emsData = $this->fetchEmsAccountDetails($accountNumber);

                if (!$emsData) {
                    $failedAccounts[] = $accountNumber; // EMS not found or error
                    continue;
                }

                // 🔹 Check outstanding balance
                if ($emsData['outstanding_balance'] > 0) {
                    $failedAccounts[] = $accountNumber;
                    continue;
                }

                // 🔹 Check payments for past 3 months
                $currentMonth = now();
                $monthsToCheck = [
                    $currentMonth->copy()->subMonths(1)->format('Y-m'),
                    $currentMonth->copy()->subMonths(2)->format('Y-m'),
                    $currentMonth->copy()->subMonths(3)->format('Y-m'),
                ];

                $payments = $this->fetchEmsPayments($accountNumber, $monthsToCheck);

                foreach ($monthsToCheck as $month) {
                    if (!isset($payments[$month]) || $payments[$month] <= 0) {
                        $failedAccounts[] = $accountNumber;
                        break;
                    }
                }
            }

        if (!empty($failedAccounts)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Some accounts did not meet the payment criteria.',
                'failed_accounts' => $failedAccounts,
            ], Response::HTTP_FORBIDDEN);
        }

        return true; // ✅ All accounts passed

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

       // return $existingUploads;

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
            'status' => isset(Auth::user()->id) ? 3 : 1,  // 3 is rico-compliance, while 1 is still started 
            'validated_by' => isset(Auth::user()->id) ? Auth::user()->email : $request->email,  // use the code to validate the email
            'comment' => $request->email
        ]);

        
            if(Auth::check()) {
                IbedcPayLogService::create([
                    'module'     => 'New Account',
                    'comment'    => '',
                    'type'       => 'Approved',
                    'module_id'  => $request->id,
                    'status'     => 'with-compliance',
                ]);
            }
        
        $update = AccoutCreaction::where('id', $checkID->id)->update([
            'status' => Auth::check() ? 'with-compliance' : 'with-dtm',
            'status_name' => 'Account Verified by ' . (Auth::check() ? Auth::user()->email : $request->email),
            'region' => $request->input('region'),
        ]);


        // $update = AccoutCreaction::where('id',$checkID->id)->update([
        //     'status' =>  isset(Auth::user()->id) ? 'with-billing' : 'with-dtm',
        //     'status_name' => 'Account Verified by'. isset(Auth::user()->id) ? Auth::user()->email : $request->email,
        //     'region' => $request['region']
        // ]);

        $uploadHouses = UploadHouses::where("id", $request->id)->first();
       // $this->generateAccount($request->id, $uploadHouses);  uncomment later

        return $this->sendSuccess([ 'customer' => $checkID, 'lecan' => "You are required to complete the the form below with a registered
        electrician/Lecan engineer, Please click on the link below to download the form and return to the app to upload same with your tracking ID",
        'link' => 'https://ibedc.com/LECAN_FORM_IBEDC.pdf' ], 'CUSTOMER APPLICATION SUCCESSFUL SUBMITTED', Response::HTTP_OK);


    }



   


    private function generateAccount($id, $uploadHouses) {
        $uploadHouses = UploadHouses::find($id);

        $buid = BusinessUnit::where("Name", strtoupper($uploadHouses->business_hub))->first();
        $servicecode = $this->getAvailableServiceCode($uploadHouses);

         $feeder = DSS::where("Assetid", $uploadHouses->dss)->first();

         $url = "http://192.168.15.157:9494/AccountGenerator/webresources/account/generate/114/FX321G9D";  // live api

        $payload = [
            'utid' => $servicecode->AREA_CODE,
            'buid' => $servicecode->BUID,
            'dssid' => $uploadHouses->dss,
            'assetId' => $feeder->Feeder_ID
        ];

        $response = Http::post($url, $payload);
        if ($response->successful()) {
             $generateAccount = $response->json();
             //$unsedAccount['accountNumbers']
             $checkUpdate = UploadHouses::where("id", $id)->update([
                "account_no" => $unsedAccount['accountNumbers']
             ]);

             //update the table with the account number
        }

        // Return a structured error response
        // return [
        //     'error' => true,
        //     'status' => $response->status(),
        //     'message' => $response->json()['error'] ?? $response->body()
        // ];

    }


     private function getAvailableServiceCode($uploadHouses)
    {
        return ServiceAreaCode::where('Service_Centre', $uploadHouses->service_center)
            ->where('BHUB', $uploadHouses->business_hub)
            ->where('number_of_customers', '<=', 1000)
            ->first();
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

        //$data = UploadHouses::where("business_hub", $user->business_hub, "service_center" => $user->service_center)->whereIn("status", ["0", "1"])->with('account')->paginate(10);

        $data = UploadHouses::where([
        'business_hub'   => $user->business_hub,
        'service_center' => $user->sc,
        ])
        ->whereIn('status', ['0', '1', '5'])
        ->with('account')
        ->paginate(10);

        return $this->sendSuccess([ 'accounts' => $data], 'CUSTOMER APPLICATION SUCCESSFUL SUBMITTED', Response::HTTP_OK);
    }




    public function reject(Request $request) {

         $request->validate([
            'id' => 'required|string',
            'comment' => 'required|string'
        ]);  


         $user = Auth::user();

        $updated = UploadHouses::where('id', $request->id)->update([
            'lecan_link' => NULL,
            'status' => 5,
            'dtm_comment' => $request->comment
        ]);

       
        IbedcPayLogService::create([
                    'module'     => 'New Account',
                    'comment'    => $request->comment,
                    'type'       => 'Rejected',
                    'module_id'  => $request->id,
                    'status'     => 'rejected',
        ]);
            

        // We need to send a mail to the customer
        $customerData = UploadHouses::where('id', $request->id)->first();

         if ($customerData) {
            // Dispatch job to send rejection email
            dispatch(new CustomerJobFeedback($customerData, $request->comment, $user));
         }


      //  dispatch(new CustomerJobFeedback($customerData));

        return $this->sendSuccess(
            ['accounts' => $updated],
            'CUSTOMER APPLICATION SUCCESSFULLY REJECTED',
            Response::HTTP_OK
        );

    }


    public function approveDTERequest(Request $request){

         $checkID =  $this->checktracking($request->tracking_id);

       // 🛑 If checktracking() returned an error response, return early
        if ($checkID instanceof \Illuminate\Http\JsonResponse) {
            return $checkID;
        }
        
         $request->validate([
            'id' => 'required|string',
            'tracking_id' => 'required|string',
            'comment' => 'required',
            'type' => 'required'
        ]);  

        if($request->type == 'approve'){

             $checkUpdate = UploadHouses::where("id", $request->id)->update([
            'status' => isset(Auth::user()->id) ? 3 : 1,
            'validated_by' => isset(Auth::user()->id) ? Auth::user()->email : $request->email,  // use the code to validate the email
            'comment' => $request->comment
             ]);

             $update = AccoutCreaction::where('id', $checkID->id)->update([
                'status' => Auth::check() ? 'with-compliance' : 'with-dtm',
                'status_name' => Auth::check() 
                    ? 'Account Verified by ' . Auth::user()->email 
                    : 'Account Verified',
                'region' => UploadHouses::where("id", $request->id)->value('region'),
            ]);

             if(Auth::check()) {
                IbedcPayLogService::create([
                    'module'     => 'New Account',
                    'comment'    => $request->comment,
                    'type'       => 'Approved',
                    'module_id'  => $request->id,
                    'status'     => 'with-compliance',
                ]);
            }

            // $update = AccoutCreaction::where('id',$checkID->id)->update([
            //     'status' =>  isset(Auth::user()->id) ? 'with-billing' : 'with-dtm',
            //     'status_name' => 'Account Verified by'. isset(Auth::user()->id) ? Auth::user()->email : '',
            //     'region' =>  UploadHouses::where("id", $request->id)->value('region')
            // ]);

        } else {

             $checkUpdate = UploadHouses::where("id", $request->id)->update([
               // 'status' => 1,
               // 'lecan_link' => 0,
                'status' => 5,
                'comment' => $request->comment
             ]);

            $email = isset($request->email) ? $request->email :  Auth::user()->email;
            // Send email with token
            Mail::raw("Your request with tracking ID was rejected. Tracking ID is: {$request->tracking_id} Comments: { $request->comment } ", function ($message) use ($email) {
                $message->to($email)
                        ->subject('Request Rejected');
            });

        }
        

        return $this->sendSuccess([ 'accounts' => $request->all()], 'CUSTOMER APPLICATION SUCCESSFUL UPDATED', Response::HTTP_OK);

    }


    public function NINService(Request $request){


        $getAuth = (new NinService)->authenticate();

        return $getAuth;
        //return $getAuth['token'];
    }

    
    



}
