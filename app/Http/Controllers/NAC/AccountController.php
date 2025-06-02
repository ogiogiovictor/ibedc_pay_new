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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;



class AccountController extends BaseAPIController
{
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

        // Check if user with same name combo exists
        $existingUser = AccoutCreaction::where('surname', $data['surname'])
            ->where('firstname', $data['firstname'])
            ->where('other_name', $data['other_name'] ?? null)
            ->first();

        if ($existingUser) {
            return $this->sendError('A user with the same name already exists. Please use your tracking ID to continu', 'ERROR', Response::HTTP_UNAUTHORIZED);
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


    //  public function complete(FinalCustomerRequest $request) {

    //     if(!$request->tracking_id) {
    //           return $this->sendError('Please provide your tracking number to cotinue', 'ERROR', Response::HTTP_UNAUTHORIZED);
    //     }

    //      // Check if tracking ID exists
    //     $existingUser = AccoutCreaction::where('tracking_id', $request->tracking_id)->first();
    //     if(!$existingUser) {
    //          return $this->sendError('Invalid Tracking ID', 'ERROR', Response::HTTP_UNAUTHORIZED);
    //     }

    //     if($existingUser && $existingUser->region != "" && $existingUser->region != "" && $existingUser->no_of_account_apply_for != ""){
    //         return $this->sendSuccess([ 'customer' => $existingUser, ], 'CUSTOMER APPLICATION SUCCESSFUL SUBMITTED', Response::HTTP_OK);
    //     }
    //      // Update account creation status
    //     $existingUser->update([
    //         'region' => $request->region,
    //         'business_hub' => $request->business_hub,
    //         'service_center' => $request->service_center,
    //         'dss' => $request->dss,
    //         'comment' => $request->comment,
    //         'meter_no' => $request->meter_no,
    //         'meter_book' => $request->meter_book,
    //         'status' => 'processing',
    //         'no_of_account_apply_for' => $request->no_of_account_apply_for
    //     ]);

    //      if($existingUser) {
    //         return $this->sendSuccess([ 'customer' => $data, ], 'CUSTOMER APPLICATION SUCCESSFUL SUBMITTED', Response::HTTP_OK);
    //      }

    //       return $this->sendError('There was an error creating your account .', 'ERROR', Response::HTTP_UNAUTHORIZED);


    //  }


    public function final(Request $request) {

        $checkID =  $this->checktracking($request->tracking_id);

       // 🛑 If checktracking() returned an error response, return early
        if ($checkID instanceof \Illuminate\Http\JsonResponse) {
            return $checkID;
        }

        $request->validate([
            'tracking_id' => 'required|string',
            'uploads' => 'required|array',
            'uploads.*.picture' => 'required|image|max:5120',
            'uploads.*.latitude' => 'required|string',
            'uploads.*.longitude' => 'required|string',
            'uploads.*.region' => 'required|string',
            'uploads.*.business_hub' => 'required|string',
            'uploads.*.service_center' => 'required|string',
            'uploads.*.dss' => 'required|string',
            'uploads.*.house_no' => 'required|string',
            'uploads.*.full_address' => 'required|string',  
        ]);

        $folder = 'customers/pictures';

        // Create folder if it doesn't exist
        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder, 0755, true);
        }       

        // you need to check if the tracking no exist() oin UploadHouse
         // ✅ Check if uploads for this tracking_id already exist
        $existingUploads = UploadHouses::where('tracking_id', $request->tracking_id)->exists();

        if ($existingUploads) {
            return $this->sendError('Uploads for this tracking ID already exist.', 'DUPLICATE ENTRY', Response::HTTP_CONFLICT);
        }

        // ✅ Extract business hub, region, and service center from first upload
         $firstUpload = $request->uploads[0] ?? null;
         if ($firstUpload) {
                $checkID->update([
                    'business_hub' => $firstUpload['business_hub'],
                    'region' => $firstUpload['region'],
                    'service_center' => $firstUpload['service_center'],
                ]);
         }

      
        foreach ($request->uploads as $upload) {
            $path = $upload['picture']->store($folder, 'public');

            UploadHouses::create([
                'customer_id' => $checkID->id,
                'tracking_id' => $request->tracking_id,
                'picture' => $path,
                'latitude' => $upload['latitude'],
                'longitude' => $upload['longitude'], 
                'region' => $upload['region'],
                'business_hub' => $upload['business_hub'], 
                'service_center' => $upload['service_center'], 
                'dss' => $upload['dss'], 
                'house_no' => $upload['house_no'], 
                'full_address' => $upload['full_address'],  
            ]);
        }

         $checkID->update([
            'status' => 'with-dtm'
        ]);

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
                $path = $upload['lecan_link']->store('lecan_uploads', 'public');

                $uploadRecord->update([
                    'lecan_link' =>  $path,
                    'status' => 0,
                ]);
            }
        }

        $checkID->update([
            'status' => 'with-dtm'
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


    
    



}
