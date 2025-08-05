<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;

use App\Services\PostPaidService;
use App\Services\PrePaidService;
use App\Enums\TransactionEnum;
use App\Http\Requests\CompletePaymentRequest;
use Illuminate\Support\Facades\Auth;

use App\Models\Wallet\WalletUser;
use App\Models\Wallet\WalletHistory;
use App\Models\Transactions\PaymentTransactions;
use App\Helpers\StringHelper;
use Illuminate\Support\Facades\DB;
use App\Models\VirtualAccountTrasactions;

class WalletPaymentConfirmation extends BaseAPIController
{
    public function CompletePayment(Request $request) {

      $authUser = Auth::user();

      $checkTrans  = PaymentTransactions::where("transaction_id", $request->transacion_id)->first();

      if($request->provider != "Wallet") {
          return $this->sendError('Error', "Error", Response::HTTP_BAD_REQUEST);
       }


    //    if(!$checkTrans->latitude) {
    //       return $this->sendError('Error', "Error", Response::HTTP_BAD_REQUEST);
    //    }


      // Ensure the user has a wallet
      if(!$authUser->wallet) {
          return $this->sendError('User does not have a wallet.', "Error", Response::HTTP_BAD_REQUEST);
       }

         // Lock the wallet record for the user
         $wallet = WalletUser::where('user_id', $authUser->id)->lockForUpdate()->first();
    
         // Check if the wallet has enough balance
         $walletBalance = (float) $wallet->wallet_amount;
         $transactionAmount = (float) abs($request->amount);
  
  
         if ($transactionAmount > $walletBalance) {
          //return "Insufficient wallet balance";
          return $this->sendError('Insufficient wallet balance. Please fund your wallet.', "Error", Response::HTTP_BAD_REQUEST);
         }

          // Check if wallet history entry already exists for this transaction ID
          $existingWalletHistory = WalletHistory::where('transactionId', $request->transacion_id)->first();

          if ($existingWalletHistory) {
              //return 'Wallet history entry already exists';
              return $this->sendError('Wallet history entry already exists for this transaction', "Error", Response::HTTP_BAD_REQUEST);
          }

          $transactionAmount = abs($request->amount);
          $authUser->wallet->decrement('wallet_amount', $transactionAmount);

          //$providerRef = VirtualAccountTrasactions::where("email" , $authUser->email)->latest()->first()->flw_ref ?: StringHelper::generateUUIDReference();
          $transaction = VirtualAccountTrasactions::where("customer_email", $authUser->email)->latest('created_at')->first();
          $providerRef = $transaction ? $transaction->flw_ref : StringHelper::generateUUIDReference();


         $chekUpdate =  PaymentTransactions::where("transaction_id", $request->transacion_id)->update([
           'providerRef' => $providerRef,
           'Descript' => "Wallet Fund Sucessfully Deducted",
           'response_status' => 1,
           'provider' => "Wallet",
           'status' => 'processing'
          ]);

           // Create wallet history entry
         WalletHistory::create([
          'user_id' => $authUser->id,
          'payment_channel' => 'Wallet',
          'price' => $request->amount,
          'transactionId' => $request->transacion_id,
          'status' => 'successful',
          'entry' => 'DR', // DR = Debit Record
          'provider_reference' => $providerRef
          ]);

          if( $chekUpdate ) {
            return $this->checkSwitch($request->account_type, $request, $checkTrans, $checkTrans);
        }else {
            return $this->sendError('Error Processing Wallet Payment', "Error!", Response::HTTP_BAD_REQUEST);
        }

    }


    private function checkSwitch($accountType, $request, $checkTrans, $payment) {

        switch($accountType){  
            case TransactionEnum::Postpaid()->value :
                return (new PostPaidService)->processService($checkTrans, $request, $payment);
            case TransactionEnum::Prepaid()->value :
                return (new PrePaidService)->processService($checkTrans, $request, $payment);
            default :
            return $this->sendError('Invalid Payment Type', "Error!", Response::HTTP_BAD_REQUEST);
        }
    }

}
