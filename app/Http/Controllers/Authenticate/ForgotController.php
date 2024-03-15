<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Jobs\PinJob;
use App\Helpers\StringHelper;
use Illuminate\Support\Facades\Hash;


class ForgotController extends BaseAPIController
{
    public function forgotPass(Request $request) {

        $validatedData = $request->validate([
            'email' => 'required|string',
            // Add other validation rules as needed
        ]);

        $user = User::where("email", $validatedData['email'])->first();

        if(!$user){
            return $this->sendError('User does not exist', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

        $pin = strval(rand(100000, 999999));
        $user->update(['pin' => $pin]);

        //dispatch a welcome email to the user
        dispatch(new PinJob($user));

        return $this->sendSuccess( [
            'payload' => $user,
            'message' => 'A PIN has been generated for your account. Please check your email for the PIN to continue the process.',
        ], 'PIN generated', Response::HTTP_OK);


    }


    public function verifyPass(Request $request){

        $validatedData = $request->validate([
            'email' => 'required|string',
            'pin' => 'required|string',
            // Add other validation rules as needed
        ]);

        $user = User::where([  "email" => $validatedData['email'], 'pin' =>$validatedData['pin'] ])->first();

        if(!$user){
            return $this->sendError('Invalid Option Sent', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }

        $transactionID = StringHelper::generateUUIDReference();
        $user->update(['pin' => $transactionID]);

        return $this->sendSuccess( [
            'payload' => $user,
            'message' => 'Successful',
        ], 'Successful', Response::HTTP_OK);

    }

    public function changePass(Request $request){

        // Validate the incoming request data
        $validatedData = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'confirm_password' => 'required|string|same:password', // Ensure confirm_password matches password
            'pin' => 'required|string',
            // Add other validation rules as needed
        ]);

        // Find the user by their email address
       // $user = User::where('email', $validatedData['email'])->first();
        $user = User::where([  "email" => $validatedData['email'], 'pin' =>$validatedData['pin'] ])->first();

        // Check if the user exists
        if ($user) {
            // Update the user's password
            $user->password = Hash::make($validatedData['password']); // Hash the new password
            $user->save();

            return $this->sendSuccess( [
                'payload' => $user,
                'message' => 'Password changed successfully',
            ], 'Successful', Response::HTTP_OK);
            // Password changed successfully
        } else {
            // User not found
            return $this->sendError('User not found', 'ERROR', Response::HTTP_UNAUTHORIZED);
        }
    }
}
