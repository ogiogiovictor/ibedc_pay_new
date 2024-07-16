<?php

namespace App\Repositories;

use App\Models\User;
use App\Interfaces\HomeRepositoryInterface;
use App\Models\ECMI\SubAccount;
use App\Models\ECMI\EcmiCustomers;

class HomeRepository implements HomeRepositoryInterface
{
   
    public function index($user_id) {

        return User::select("id", "meter_no_primary", "name", "email", "phone")->where("id", $user_id)->with('wallet')->get();
    }

    public function checkPin($user_email, $pin){
        return User::where(["email" => $user_email, "pin" => $pin])->first();
    }

    public function getSubAccount($accountno){
        return SubAccount::where(["AccountNo" => EcmiCustomers::where("MeterNo", $accountno)->value('AccountNo'), "SubAccountAbbre" => "OUTBAL"])->first();
    }

    public function getSubAccountFPUnit($accountno){
        $subAccountBalFpUnit = SubAccount::where(["AccountNo" => EcmiCustomers::where("MeterNo", $accountno)->value('AccountNo'), "SubAccountAbbre" => 'FPUNIT'])->first()->Balance;
    }

    public function userprofile($user_id) {
        return User::where("id", $user_id)->first();
    }

    public function updateProfile($userRequest, $userid){
        
      // Retrieve the user
        $user = User::find($userid);

        if (!$user) {
            // User not found
            return null; // Or handle this case as appropriate
        }

        $checkPhone = User::where('phone', $userRequest->phone)->first();

        if($checkPhone) {
            return "Error! Phone Number Exists";
        }

        if (str_starts_with($userRequest->email, 'noemail')) {
             //we need to check if the email you want to update already exist before even updating..
            $user->email = isset($userRequest->email) ? $userRequest->email : $user->email;
            $user->phone = isset($userRequest->phone) ? $userRequest->phone : $user->phone;
        }

        if (str_starts_with($userRequest->email, 'default')) {
            //we need to check if the email you want to update already exist before even updating..
           $user->email = isset($userRequest->email) ? $userRequest->email : $user->email;
           $user->phone = isset($userRequest->phone) ? $userRequest->phone : $user->phone;
       }

        // Update user attributes based on the request
        $user->meter_no_primary = isset($userRequest->meter_no_primary) ? $userRequest->meter_no_primary : $user->meter_no_primary;
        $user->phone = isset($userRequest->phone) ? $userRequest->phone : $user->phone;

        if($userRequest->password){
            $user->password = isset($userRequest->password) ? bcrypt($userRequest->password) : $user->password;
        }

        //we need to check if the email you want to update already exist before even updating..
        //$user->email = isset($userRequest->email) ? $userRequest->email : $user->email;
        //$user->phone = isset($userRequest->phone) ? $userRequest->phone : $user->phone;

        // Save the changes
        $user->save();

        return $user;

    }

}
