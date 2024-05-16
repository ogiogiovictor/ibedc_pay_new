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

        $user_status = User::where("email", $request->email)->first();

        if(!$user_status) {
            // User not found with the provided email
            return $this->sendError('User not found', 'ERROR', Response::HTTP_NOT_FOUND);
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

        //Check if the meter is already mapped with the user
        $checkifExist = User::where("meter_no_primary", $request->meter_no)->first();


        if(!$checkifExist){
            return $this->sendError('This meter no is not mapped to your profile. Please register your account', "FALSE", Response::HTTP_UNAUTHORIZED);
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
}
