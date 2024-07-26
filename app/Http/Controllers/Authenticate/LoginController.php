<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\AccountLoginRequest;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet\WalletUser;
use App\Jobs\PinJob;
use App\Models\EMS\ZoneCustomers;
use App\Models\ECMI\EcmiCustomers;
use App\Helpers\StringHelper;
use App\Services\AppService;


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

    public function authLogin(AccountLoginRequest $request){

        $checkService = (new AppService)->processApp("System");

        if ($checkService instanceof \Illuminate\Http\JsonResponse) {
            return $checkService; // Return the downtime error response
        }

        //Check if the meter is already mapped with the user
        $checkifExist = User::where("meter_no_primary", $request->meter_no)->first();


        if(!$checkifExist){
            return $this->sendError('This meter no is not mapped to your profile. Please login with your email/password or register an account', "FALSE", Response::HTTP_UNAUTHORIZED);
        }

        if($checkifExist->status != 1){
            return $this->sendError('User Not Activated', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

         $getResponse = $this->checkWhichType($request->account_type, $request->meter_no);

        //If the customer is not a Customer of IBEDC just return that the account number does not exist or no record found
        if(!$getResponse){
            return $this->sendError('Invalid Meter/Account No', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }
        //the use the account number to check if it exist in IBEDCPay or if the user if fully registered.

        //If the user is not registered, redirect the user to the registration page to get the user information to complete the process

        $user = $checkifExist;

        // You may want to set up the authentication manually
        Auth::login($user);

        // Check if the user is authenticated
        if (Auth::check()) {
            // User is authenticated, proceed with sending the success response
            return $this->sendSuccess([
                'user' => $user,
                'token' => $user->createToken('Authorization')->plainTextToken,
                'wallet' => $user->wallet,
                'account' => $user->virtualAccount,
            ], 'LOGIN SUCCESSFUL', Response::HTTP_OK);
        } else {
            // Authentication failed for some reason
            // Handle this case as per your application's logic
        }

        //Then send a message to the user that an activity has been performed in the user account and if the user is not the person send a flag
        // $userdetails = User::where(["meter_no_primary" => $request->meter_no, 'account_type' => $request->account_type])->first();
        // if(!$userdetails) {
        //     // User not found with the provided email
        //     return $this->sendError('You account is not fully provisioned on IBEDCPay, Kindly register your account', 'ERROR', Response::HTTP_NOT_FOUND);
        // }

        
    }

    private function checkWhichType($accountType, $meterno){

        if($accountType == 'Postpaid'){
           $customerData = ZoneCustomers::where("AccountNo", $meterno)->first();
           return $customerData;
        }else {
            $customerData = EcmiCustomers::where("MeterNo", $meterno)->first();
            return $customerData;
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

        $checkService = (new AppService)->processApp("System");

        if ($checkService instanceof \Illuminate\Http\JsonResponse) {
            return $checkService; // Return the downtime error response
        }

        $getResponse = $this->checkWhichType($request->account_type, $request->meter_no);

        //If the customer is not a Customer of IBEDC just return that the account number does not exist or no record found
        if(!$getResponse){
            return $this->sendError('Invalid Meter/Account No', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }
       
         //Check if the meter is already mapped with the user
        $checkifExist = User::where("meter_no_primary", $request->meter_no)->first();

        if($checkifExist) {
            // Do auth check and login 
            $user = $checkifExist;

             // You may want to set up the authentication manually
            Auth::login($user);

            if (Auth::check()) {
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
       $checkPhone = User::where("phone", $getResponse->Mobile)->first();
       
      //  $newPhone = $this->generatePhone();
       // $newPhone = "0".$getResponse->Mobile ?: $getResponse->Telephone;  8062665117
       

        if (!$checkPhone) {
            // Handle the case where the phone number already exists, maybe generate a new unique phone number
            $newPhone = $this->generatePhone(); // Implement your own logic for generating a unique phone number
        } else  {
            // Check if $getResponse->Mobile is set and not null, otherwise use $getResponse->Telephone
            if (isset($getResponse->Mobile) && $getResponse->Mobile !== null) {
                $newPhone = $getResponse->Mobile;
            } else if (isset($getResponse->Telephone) && $getResponse->Telephone !== null) {
                $newPhone = $getResponse->Telephone;
            } else {
                // If both Mobile and Telephone are not set or are null, handle accordingly
                $newPhone = $this->generatePhone(); // Replace with your default logic
            }
           // $newPhone = "0". isset($getResponse->Mobile)  ? $getResponse->Mobile : $getResponse->Telephone;
        }

     
            if($request->account_type == 'Prepaid'  || $request->account_type == 'prepaid') {

                 // Extract the provided email or use a default email if not available
             $email = isset($getResponse->EMail) ? $getResponse->EMail : "default-".$transactionID."@ibedc.com";

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
                    'name' => $getResponse->Surname. " ". $getResponse->OtherNames,
                    'email' => $email, // isset($getResponse->EMail) ? $getResponse->EMail :  "default-".$transactionID."@ibedc.com",
                    'status' => 1,
                    'meter_no_primary' => $getResponse->MeterNo,
                    'account_type' => $request->account_type,
                    'password' => $transactionID,
                    'phone' => $newPhone, //$this->generatePhone(), //  //"0".$getResponse->Mobile ?: $getResponse->Telephone,
                    'pin' => 0
                ]);

                Auth::login($addCustomer);

                if (Auth::check()) {
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
                $checkPhone = User::where("phone", $getResponse->Mobile)->first();

                if ($checkPhone) {
                    // If phone already exists, generate a new unique phone number
                    $newPhone = $this->generateUniquePhone($getResponse->Mobile);
                } else {
                    // Use the provided phone number or generate if not available
                    $newPhone = $getResponse->Mobile ?? $this->generatePhone();
                }

                //The account is a postpaid account
                $addCustomer  = User::create([
                    'name' => str_replace(' ', '', $getResponse->Surname). " ". $getResponse->FirstName,
                    'email' => isset($getResponse->email) ? $getResponse->email :  "default-".$transactionID."@ibedc.com",
                    'status' => 1,
                    'meter_no_primary' => $getResponse->AccountNo,
                    'account_type' => $request->account_type,
                    'password' => $transactionID,
                    'phone' => $newPhone, // "0".$getResponse->Mobile ?: $getResponse->Mobile,
                    'pin' => 0
                ]);

                Auth::login($addCustomer);

                if (Auth::check()) {
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
