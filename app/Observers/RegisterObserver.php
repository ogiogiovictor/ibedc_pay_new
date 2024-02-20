<?php

namespace App\Observers;

use App\Models\User;
use App\Helpers\UniqueNo;
use App\Models\Wallet\WalletUser;
use Illuminate\Support\Facades\DB;

class RegisterObserver
{
    public function creating(User $user) {
        // Logic before user creation
        $user->user_code = (new UniqueNo)->generate(fn($companyNo) => DB::table('users')->select('user_code')->where('user_code', $companyNo)->exists(), 15, true, 'FRT' );
    }

    public function created(User $user) {
        // Logic after user creation //Create the Wallet Amount
       // Check if a wallet with the specified user_id exists
        if (!WalletUser::where('user_id', $user->id)->exists()) {
            // Wallet does not exist, create a new one
            WalletUser::create([
                'user_id' => $user->id,
                'wallet_amount' => 0.00,
            ]);

            // Additional logic after creating the wallet if needed
        } else {
            // Wallet already exists, handle accordingly
            // You can add additional logic here if needed
        }

        //Send Welcome Email
    }

    public function updating(User $user) {
        // Logic before user update
    }

    public function updated(User $user) {
        // Logic after user update
    }

    public function deleting(User $user) {
        // Logic before user deletion
    }


}
