<?php

namespace App\Services;

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Models\CommissionHistory;
use App\Models\Transactions\PaymentTransactions;
use Illuminate\Support\Facades\Auth;
use App\Models\CommissionSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class CommissionService  extends BaseAPIController
{
    public function processCommission($checkRef, $commission, $commissionPercent)
    {

        $checkIfExist = CommissionHistory::where("transaction_id", $checkRef->id)->first();
        $user = Auth::user();

        //First check if the payment is successful and the commission for that payment is already paid
        if($checkRef->status == "success"){
            //Then check if the commission has already been paid
            if(!$checkIfExist && $user->id == $checkRef->user_id){
                //Then create the commission
                $createCommission = CommissionHistory::create([
                    'user_id' => $user->id,
                    'amount_paid' => $checkRef->amount,
                    'commission_amount' =>  Crypt::encrypt($commission),
                    'commission_percent' => $commissionPercent,
                    'acount_type' => 'Postpaid',
                    'account_id' => $checkRef->account_number,
                    'agency' => $checkRef->agency,
                    'transaction_id' => $checkRef->id
                ]);

                if($user->wallet) {
                    $currentCommission = $user->wallet->commission_amount;
                    // Check if the current commission is NULL or 0
                    if (is_null($currentCommission) || $currentCommission == 0) {
                        $decryptedCurrentCommission = 0;
                    } else {
                        // Decrypt the current commission amount
                        $decryptedCurrentCommission = Crypt::decrypt($currentCommission);
                    }

                    // Add the new commission
                    $newCommissionAmount = $decryptedCurrentCommission + abs($commission);

                    // Encrypt the new commission amount
                    $encryptedNewCommissionAmount = Crypt::encrypt($newCommissionAmount);

                    // Save the new encrypted commission amount back to the wallet
                    $user->wallet->update(['commission_amount' => $encryptedNewCommissionAmount]);

                   // $addtoWallet = $user->wallet->increment('commission_amount', $commission);
                } else {
                    \Log::warning('User wallet not found for user ID: ' . $user->id);
                }

              //  if($user->wallet){
                  //  $addtoWallet =  $user->wallet ? $user->wallet->increment('commission_amount', $commission) : 0;
              //  }
               

            }

        } else {

            \Log::info('No Commission Response: ' . json_encode($checkIfExist));

        }
          
    }


    public function outstandingCommission() {

    }
}
