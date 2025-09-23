<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\AccountLoginRequest;
use App\Models\User;
use App\Models\CustomerAccount;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet\WalletUser;
use App\Jobs\PinJob;
use App\Models\EMS\ZoneCustomers;
use App\Models\ECMI\EcmiCustomers;
use App\Helpers\StringHelper;
use App\Services\AppService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;


class LoginController extends BaseAPIController
{

   
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LoginRequest $request)
    {


        $checkService = (new AppService)->processApp("System");

        if ($checkService instanceof \Illuminate\Http\JsonResponse) {
            return $checkService; // Return the downtime error response
        }

        $user_status = User::where("email", $request->email)->first();

        if(!$user_status) {
            // User not found with the provided email
            return $this->sendError('User not found', 'ERROR!!!', Response::HTTP_NOT_FOUND);
        }
        

        if($user_status->status == 0 && $user_status->pin){

            $pin = strval(rand(100000, 999999));
            $user_status->update(['pin' => $pin]);

             //dispatch a welcome email to the user
              dispatch(new PinJob($user_status));

            return $this->sendError('Enter Pin To Activate', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

        if($user_status->status != 1){
            return $this->sendError('User Not Activated', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

        if($request->expectsJson()) {

            // Validate the login request
        $validatedData = $request->validated();

        // Attempt to log in the user
        if (Auth::attempt($validatedData)) {
            // Authentication passed...
            $user = Auth::user();

            if($user->id == '647730') {
                    return $this->sendError('ERROR', 'ERROR', Response::HTTP_UNAUTHORIZED);
             }

           
             DB::disconnect('sqlsrv');
            // You can customize the response based on your needs
            return $this->sendSuccess([
                'user' => $user,
                'token' => $user->createToken('Authorization')->plainTextToken,
                'wallet' => $user->wallet,
                'account' => $user->virtualAccount,
            ], 'LOGIN SUCCESSFUL', Response::HTTP_OK);
        }

        // Authentication failed
        return $this->sendError('Invalid credentials', 'ERROR', Response::HTTP_UNAUTHORIZED);

        }else {
            return $this->sendError("Error", "Error Loading Data, Something went wrong(NOT JSON())", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

   
    private function checkWhichType($accountType, $meterno){

        if($accountType == 'Postpaid'){

            $cleaned = str_replace(['/', '-'], '',  $meterno);
            $URL = env("POSTPAID_LINK") . "Identification/" . env("POSTPAID_KEY") . "/" . $cleaned . "/" . env("POSTPAID_TOKEN") . "?postpaid=true";

            /** ✅ 7. Make GET request */
            $response = Http::withoutVerifying()
                    ->withHeaders([
                        'Connection'    => 'close',
                    ])
                    ->retry(3, 2000)
                    ->timeout(30)
                    ->get($URL);


             if ($response->successful()) {
               return  $raw = $response->json();

        }

        //    $customerData = ZoneCustomers::where("AccountNo", $meterno)->first();
        //    return $customerData;


        }else {

            $URL = env("PREPAID_LINK") . "Identification/" . env("PREPAID_KEY") . "/" . $meterno . "/" . env("PREPAID_TOKEN") . "?referencetype=accountnumber&postpaid=false";

            
            $response = Http::withoutVerifying()
                    ->withHeaders([
                        'Connection'    => 'close',
                    ])
                    ->retry(3, 2000)
                    ->timeout(30)
                    ->get($URL);

                 if ($response->successful()) {

               return  $raw = $response->json();

             }
            // $customerData = EcmiCustomers::where("MeterNo", $meterno)->first();
            // return $customerData;
        }
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

    public function generatePhone() {
         // Start with "081"
        $prefix = '090';

        // Generate the remaining 8 digits
        $remainingDigits = '';

        // Generate 8 random digits
        for ($i = 0; $i < 8; $i++) {
            $remainingDigits .= mt_rand(0, 9);
        }

        // Combine prefix with random digits
        $phoneNumber = $prefix . $remainingDigits;

        return $phoneNumber;
    }





    public function authLoginTest(AccountLoginRequest $request){

         // ✅ Step 1: Simple Rate Limiting (5 attempts per IP per minute)
        $ip = $request->ip();
        $key = "login:attempts:{$ip}";

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return $this->sendError('Too Many Requests', 'Please try again later', Response::HTTP_TOO_MANY_REQUESTS);
        }
        RateLimiter::hit($key, 60); // lockout for 60 seconds

        $checkService = (new AppService)->processApp("System");

        if ($checkService instanceof \Illuminate\Http\JsonResponse) {
            return $checkService; // Return the downtime error response
        }

        $getResponse = $this->checkWhichType($request->account_type, $request->meter_no);

        //If the customer is not a Customer of IBEDC just return that the account number does not exist or no record found
        if(!$getResponse || !$getResponse["customerName"]){
            return $this->sendError('ERROR', 'Invalid Meter/Account No', Response::HTTP_UNAUTHORIZED);
        }
       
         //Check if the meter is already mapped with the user
        $checkifExist = User::where("meter_no_primary", $request->meter_no)->first();


        //$updateEmail = str_starts_with($checkifExist->email, 'default') || str_starts_with($checkifExist->email, 'noemail');

        // if(!$updateEmail){
        //     return $this->sendError('ERROR', 'You can only login with Username/Password for this meter', Response::HTTP_UNAUTHORIZED);
        // }

        if($checkifExist) {
            // Do auth check and login 
            $user = $checkifExist;

             // You may want to set up the authentication manually
            Auth::login($user);

            if (Auth::check()) {


                if($user->id == '647730') {
                    return $this->sendError('ERROR', 'ERROR', Response::HTTP_UNAUTHORIZED);
                }

                 DB::disconnect('sqlsrv');
                // User is authenticated, proceed with sending the success response
                return $this->sendSuccess([
                    'user' => $user,
                    'token' => $user->createToken('Authorization')->plainTextToken,
                    'wallet' => $user->wallet,
                    'account' => $user->virtualAccount,
                ], 'LOGIN SUCCESSFUL', Response::HTTP_OK);
            }

        } else {

            // $checkifExist = User::where("id", "1543")->first();
            // return $getResponse;
            $transactionID = StringHelper::generateUUIDReference().rand(0, 10);
            //Create an account for the user
            $checkPhone = User::where("phone", $getResponse["phoneNumber"])->first();
            
            //  $newPhone = $this->generatePhone();
            // $newPhone = "0".$getResponse->Mobile ?: $getResponse->Telephone;  8062665117
            

        if (!$checkPhone) {
            // Handle the case where the phone number already exists, maybe generate a new unique phone number
            $newPhone = $this->generatePhone(); // Implement your own logic for generating a unique phone number
        } else  {
            // Check if $getResponse->Mobile is set and not null, otherwise use $getResponse->Telephone
            if (isset($getResponse["phoneNumber"]) && $getResponse["phoneNumber"] !== null) {
                $newPhone = $getResponse["phoneNumber"];
            } else if (isset($getResponse["phoneNumber"]) && $getResponse["phoneNumber"] !== null) {
                $newPhone = $getResponse["phoneNumber"];
            } else {
                // If both Mobile and Telephone are not set or are null, handle accordingly
                $newPhone = $this->generatePhone(); // Replace with your default logic
            }
           // $newPhone = "0". isset($getResponse->Mobile)  ? $getResponse->Mobile : $getResponse->Telephone;
        }

     
            if($request->account_type == 'Prepaid'  || $request->account_type == 'prepaid') {  //email

                 // Extract the provided email or use a default email if not available
             $email = isset($getResponse["email"]) ? $getResponse["email"] : "default-".$transactionID."@ibedc.com";

             // Check if the email already exists in the users table
            if (User::where('email', $email)->exists()) {
                // If the email exists, generate a unique default email
                $email = "default-".$transactionID."@ibedc.com";
                
                // Add a counter to ensure the generated email is unique
                // $counter = 1;
                // while (User::where('email', $email)->exists()) {
                //     $email = "default-".$transactionID."-".$counter."@ibedc.com";
                //     $counter++;
                // }
            }
            

                $addCustomer  = User::create([
                    'name' => $getResponse["customerName"],
                    'email' => $email, 
                    'status' => 1,
                    'meter_no_primary' => $getResponse["accountNumber"],
                    'account_type' => $request->account_type,
                    'password' => $transactionID,
                    'phone' => $newPhone, //$this->generatePhone(), //  //"0".$getResponse->Mobile ?: $getResponse->Telephone,
                    'pin' => 0
                ]);

                Auth::login($addCustomer);

                if (Auth::check()) {

                     DB::disconnect('sqlsrv');
                    return $this->sendSuccess(
                        [
                        'user' => $addCustomer,
                        'token' => $addCustomer->createToken('Authorization')->plainTextToken,
                        'wallet' => $addCustomer->wallet,
                        'account' => $addCustomer->virtualAccount,
                    ], 'LOGIN SUCCESSFUL', Response::HTTP_OK);
                }
            } else {


                  // Check if the phone number exists and handle it accordingly
                $checkPhone = User::where("phone", $getResponse["phoneNumber"])->first();

                if ($checkPhone) {
                    // If phone already exists, generate a new unique phone number
                    $newPhone = $this->generateUniquePhone($getResponse["phoneNumber"]);
                } else {
                    // Use the provided phone number or generate if not available
                    $newPhone = $getResponse["phoneNumber"] ?? $this->generatePhone();
                }

                //The account is a postpaid account
                $addCustomer  = User::create([
                    'name' => $getResponse["customerName"],
                    'email' => isset($getResponse['email']) ? $getResponse['email'] :  "default-".$transactionID."@ibedc.com",
                    'status' => 1,
                    'meter_no_primary' => $getResponse["accountNumber"],
                    'account_type' => $request->account_type,
                    'password' => $transactionID,
                    'phone' => $newPhone, // "0".$getResponse->Mobile ?: $getResponse->Mobile,
                    'pin' => 0
                ]);

                Auth::login($addCustomer);

                if (Auth::check()) {

                     DB::disconnect('sqlsrv');
                return $this->sendSuccess([
                    'user' => $addCustomer,
                    'token' => $addCustomer->createToken('Authorization')->plainTextToken,
                    'wallet' => $addCustomer->wallet,
                    'account' => $addCustomer->virtualAccount,
                ], 'LOGIN SUCCESSFUL', Response::HTTP_OK);
                }
            }
      

       
            // but if not available we need to mapp it to a default account 
            //Default user is 1543
        }

        
    }








    public function authLoginTestCustomer(AccountLoginRequest $request){

         // ✅ Step 1: Simple Rate Limiting (5 attempts per IP per minute)
        $ip = $request->ip();
        $key = "login:attempts:{$ip}";

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return $this->sendError('Too Many Requests', 'Please try again later', Response::HTTP_TOO_MANY_REQUESTS);
        }
        RateLimiter::hit($key, 60); // lockout for 60 seconds

        $checkService = (new AppService)->processApp("System");

        if ($checkService instanceof \Illuminate\Http\JsonResponse) {
            return $checkService; // Return the downtime error response
        }

        $getResponse = $this->checkWhichType($request->account_type, $request->meter_no);

        //If the customer is not a Customer of IBEDC just return that the account number does not exist or no record found
        if(!$getResponse || !$getResponse["customerName"]){
            return $this->sendError('ERROR', 'Invalid Meter/Account No', Response::HTTP_UNAUTHORIZED);
        }
       
         //Check if the meter is already mapped with the user
        $checkifExist = CustomerAccount::where("meter_no_primary", $request->meter_no)->orWhere("old_account_number",  $request->meter_no)->first();


        if($checkifExist) {
            // Do auth check and login 
            $user = $checkifExist;

             // You may want to set up the authentication manually
            //Auth::login($user);
            Auth::guard('customer')->login($user);

            //if (Auth::check()) {
            if (Auth::guard('customer')->check()) {


                if($user->id == '647730') {
                    return $this->sendError('ERROR', 'ERROR', Response::HTTP_UNAUTHORIZED);
                }


                 DB::disconnect('sqlsrv');
                // User is authenticated, proceed with sending the success response
                return $this->sendSuccess([
                    'user' => $user,
                    'token' => $user->createToken('Authorization')->plainTextToken,
                    'wallet' => $user->wallet,
                    'account' => $user->virtualAccount,
                ], 'LOGIN SUCCESSFUL', Response::HTTP_OK);
            }

        } else {



     
            if($request->account_type == 'Prepaid'  || $request->account_type == 'prepaid') {  //email

                $transactionID = StringHelper::generateUUIDReference().rand(0, 10);

                $newPhone = isset($getResponse["phoneNumber"]) ? $getResponse["phoneNumber"] : $this->generatePhone();
                //Create an account for the user
                $checkPhone = CustomerAccount::where("phone", $newPhone)->first();
                
                if (!$checkPhone) {
                    $newPhone = $this->generatePhone(); // Implement your own logic for generating a unique phone number
                } else  {
                    $newPhone = $getResponse["phoneNumber"];
                }

                 // Extract the provided email or use a default email if not available
                $email = isset($getResponse["email"]) && $getResponse["email"] != ""  ? $getResponse["email"] : "default-".$transactionID."@ibedc.com";

        
                $addCustomer  = CustomerAccount::create([
                    'name' => $getResponse["customerName"],
                    'email' => $email, 
                    'status' => 1,
                    'meter_no_primary' => $request->meter_no,  //$getResponse["accountNumber"],
                    'account_type' => $request->account_type,
                    'password' => $transactionID,
                    'phone' => $newPhone, //$this->generatePhone(), //  //"0".$getResponse->Mobile ?: $getResponse->Telephone,
                    'pin' => 0,
                    'old_account_number' => $request->meter_no

                ]);

               // Auth::login($addCustomer);
                Auth::guard('customer')->login($addCustomer);

                if (Auth::guard('customer')->check()) {
                //if (Auth::check()) {

                     DB::disconnect('sqlsrv');
                    return $this->sendSuccess(
                        [
                        'user' => $addCustomer,
                        'token' => $addCustomer->createToken('Authorization')->plainTextToken,
                        'wallet' => $addCustomer->wallet,
                        'account' => $addCustomer->virtualAccount,
                    ], 'LOGIN SUCCESSFUL', Response::HTTP_OK);
                }
            } else {

                $transactionID = StringHelper::generateUUIDReference().rand(0, 10);
                //Create an account for the user

                  // Check if the phone number exists and handle it accordingly
                if(isset($getResponse["phoneNumber"])) {
                     $newPhone = $getResponse["phoneNumber"]; // CustomerAccount::where("phone", $getResponse["phoneNumber"])->first();
                } else {
                     $newPhone = $this->generatePhone();
                }

                
             
                //The account is a postpaid account
                $addCustomer  = CustomerAccount::create([
                    'name' => $getResponse["customerName"],
                    'email' => "default-".$transactionID."@ibedc.com",
                    'status' => 1,
                    'meter_no_primary' => $getResponse["accountNumber"],
                    'account_type' => $request->account_type,
                    'password' => $transactionID,
                    'phone' =>  $newPhone, // "0".$getResponse->Mobile ?: $getResponse->Mobile,
                    'pin' => 0,
                     'old_account_number' => $request->meter_no
                ]);

                Auth::guard('customer')->login($addCustomer);
                //Auth::login($addCustomer);

                if (Auth::guard('customer')->check()) {
                //if (Auth::check()) {

                     DB::disconnect('sqlsrv');
                return $this->sendSuccess([
                    'user' => $addCustomer,
                    'token' => $addCustomer->createToken('Authorization')->plainTextToken,
                    'wallet' => $addCustomer->wallet,
                    'account' => $addCustomer->virtualAccount,
                ], 'LOGIN SUCCESSFUL', Response::HTTP_OK);
                }
            }
       
            // but if not available we need to mapp it to a default account 
            //Default user is 1543
        }

        
    }



    private function generateUniquePhone($basePhone)
    {
        $counter = 1;
        $newPhone = $basePhone;

        // Loop to generate a unique phone number
        while (User::where('phone', $newPhone)->exists()) {
            $newPhone = $basePhone .  $counter;

            $counter++;
        }

        return $newPhone;
    }



}
