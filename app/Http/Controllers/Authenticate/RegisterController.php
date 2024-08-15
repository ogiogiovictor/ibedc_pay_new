<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Http\Requests\RegisterRequest;
use App\Jobs\RegistrationJob;
use Illuminate\Support\Facades\Auth;
use App\Events\VirtualAccount;
use Spatie\Permission\Models\Role;


class RegisterController extends BaseAPIController
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
    public function store(RegisterRequest $request)
    {
        
        
        //create the user
        $user = User::create($request->all());

        $pin = strval(rand(100000, 999999));
        $user->update(['pin' => $pin]);

        isset($request->authority) ? $user->assignRole(strtolower($request->authority)) : '';

        //dispatch a welcome email to the user
        dispatch(new RegistrationJob($user));

       // VirtualAccount::dispatch($user);

       // event(new VirtualAccount($user));

        return $this->sendSuccess( [
            'payload' => $user,
            'message' => 'A PIN has been generated for your account. Please check your email for the PIN to complete the registration process.',
        ], 'PIN generated', Response::HTTP_OK);

       // return BaseAPIController::sendSuccess($user,  "SUCCESS", Response::HTTP_OK); 
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

    public function verifyPin(Request $request){

        if(!$request->pin || !$request->email){
            return $this->sendError('Please enter Pin', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

        // Get the authenticated user

        $user = User::where(['pin' => $request->pin, 'email' => $request->email])->first();


        // Check if the provided PIN matches the stored hashed PIN
        if ($user && $request->pin == $user->pin) {
            // PIN is correct

            // Clear the stored PIN (optional, depending on your requirements)
            $user->update(['pin' => "0", "status" => 1]);

            // Return the user object, token, and authorization
            return $this->sendSuccess([
                'user' => $user,
            ], 'Pin successfully Verified', Response::HTTP_OK);
        }

        // Incorrect PIN
        return $this->sendError('Invalid PIN', 'The provided PIN is incorrect', Response::HTTP_UNAUTHORIZED);


    }

    public function retyCode(Request $request){  // This pin will expire after 10mins do a job for that to remove pin and change to 0
       
        if(!$request->email){
            return $this->sendError('Please send email', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

        $user = User::where('email', $request->email)->first();


        // Check if the provided PIN matches the stored hashed PIN
        if ($user) {
            // PIN is correct

            // Clear the stored PIN (optional, depending on your requirements)
            $pin = strval(rand(100000, 999999));
            $user->update(['pin' => $pin]);

            //dispatch a welcome email to the user
            dispatch(new RegistrationJob($user));

            // Return the user object, token, and authorization
            return $this->sendSuccess([
                'user' => $user,
            ], 'Pin successfully Generated', Response::HTTP_OK);
        }

        return $this->sendError('User Does Not Exist', 'ERROR', Response::HTTP_UNAUTHORIZED);


    }


    public function addMeter(Request $request){

        if(!$request->email || !$request->meter_no || !$request->account_type){
            return $this->sendError('Please enter your meter or account number and Select Account Type', 'ERROR', Response::HTTP_UNAUTHORIZED);  //meter_no_primary
        }

        $checkForMeter = User::where('email', $request->email)->first();

        if(!$checkForMeter->meter_no_primary) {
            $checkForMeter->update(['meter_no_primary' => $request->meter_no, 'account_type' => $request->account_type]);

            return $this->sendSuccess([
                'user' => $checkForMeter,
            ], 'User Meter Successfully Updated', Response::HTTP_OK);
        } else {

            return $this->sendSuccess([
                'user' => "Continue",
            ], 'User Meter Already Exist. Use your meter/account number to login if you have not created a profile', Response::HTTP_OK);
        }


    }

    
}
