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

        //First check if the meter no / account number is a customer of IBEDC

        //If the customer is not a Customer of IBEDC just return that the account number does not exist or no record found

        //the use the account number to check if it exist in IBEDCPay or if the user if fully registered.

        //If the user is not registered, redirect the user to the registration page to get the user information to complete the process

        //If the user exist, use the user email to login the user.

        //Then send a message to the user that an activity has been performed in the user account and if the user is not the person send a flag
        
        $userdetails = User::where(["meter_no_primary" => $request->meter_no, 'account_type' => $request->account_type])->first();

        if(!$userdetails) {
            // User not found with the provided email
            return $this->sendError('You account is not fully provisioned on IBEDCPay, Kindly register your account', 'ERROR', Response::HTTP_NOT_FOUND);
        }

        
        if($userdetails->status != 1){
            return $this->sendError('User Not Activated', 'ERROR', Response::HTTP_UNAUTHORIZED);
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
