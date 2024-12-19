<?php

namespace App\Repositories\Payment;
use App\Interfaces\PayableInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet\WalletUser;
use App\Models\Wallet\WalletHistory;
use App\Models\Transactions\PaymentTransactions;
use App\Helpers\StringHelper;
use Illuminate\Support\Facades\DB;

class WalletPaymentRepository extends BaseApiController implements PayableInterface
{
    public $checkTrans;
    public $type;

    public function __construct($type, $checkTrans) {
        $this->checkTrans = $checkTrans;
        $this->type = $type;
    }


    public function pay()
    {
       
       $authUser = Auth::user();
 
        // Ensure the user has a wallet
      if (!$authUser->wallet) {
          return $this->sendError('User does not have a wallet.', "Error", Response::HTTP_BAD_REQUEST);
       }


       // Lock the wallet record for the user
       $wallet = WalletUser::where('user_id', $authUser->id)->lockForUpdate()->first();

       // Check if the wallet has enough balance
       $walletBalance = (float) $wallet->wallet_amount;
       $transactionAmount = (float) abs($this->checkTrans->amount);


       if ($transactionAmount > $walletBalance) {
        //return "Insufficient wallet balance";
        return $this->sendError('Insufficient wallet balance. Please fund your wallet.', "Error", Response::HTTP_BAD_REQUEST);
      }


         // Check if wallet history entry already exists for this transaction ID
        $existingWalletHistory = WalletHistory::where('transactionId', $this->checkTrans->transaction_id)->first();
        if ($existingWalletHistory) {
            // Entry already exists, return error or handle as needed
            return 'Wallet history entry already exists';
            return $this->sendError('Wallet history entry already exists for this transaction', "Error", Response::HTTP_BAD_REQUEST);
        }

         // Start a database transaction for atomic operations
         DB::beginTransaction();

        try {

           // deduct the money from the wallet  $transactionAmount = abs($this->checkTrans->amount);
           $authUser->wallet->decrement('wallet_amount', $transactionAmount);

           PaymentTransactions::where("transaction_id", $this->checkTrans->transaction_id)->update([
            'providerRef' => StringHelper::generateUUIDReference(),
            'Descript' => "Wallet Fund Sucessfully Deducted",
            'response_status' => 1,
            'provider' => "Wallet",
           ]);

           // Create wallet history entry
           WalletHistory::create([
            'user_id' => $authUser->id,
            'payment_channel' => 'Wallet',
            'price' => $this->checkTrans->amount,
            'transactionId' => $this->checkTrans->transaction_id,
            'status' => 'successful',
            'entry' => 'DR', // DR = Debit Record
            ]);

             // Commit the transaction
             DB::commit();

             // Send success response
            // return $this->checkTrans;
             return $this->sendSuccess($this->checkTrans, "Success", Response::HTTP_OK);
 


         } catch (\Exception $e) {
            // Rollback the transaction in case of failure
            DB::rollBack();

            // Log the error for further investigation
          //  Log::error("Payment processing failed: " . $e->getMessage());

            // Return an error response
            //return 'Error';
           return $this->sendError($e->getMessage(), "Error", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
      
    }
}
