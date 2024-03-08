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
       // return $authUser->wallet->wallet_amount;
       if($this->checkTrans->amount > $authUser->wallet->wallet_amount) {
        return $this->sendError('Low Wallet Amount. Please fund Wallet', "Error", Response::HTTP_BAD_REQUEST);
       }

         // Check if wallet history entry already exists for this transaction ID
        $existingWalletHistory = WalletHistory::where('transactionId', $this->checkTrans->transaction_id)->first();
        if ($existingWalletHistory) {
            // Entry already exists, return error or handle as needed
            return $this->sendError('Wallet history entry already exists for this transaction', "Error", Response::HTTP_BAD_REQUEST);
        }

       // deduct the money from the wallet
       $authUser->wallet->decrement('wallet_amount', $this->checkTrans->amount);

          //update the transaction reference
          $update = PaymentTransactions::where("transaction_id", $this->checkTrans->transaction_id)->update([
            'providerRef' => StringHelper::generateUUIDReference(),
            'Descript' => "Wallet Fund Sucessfully Deducted",
            'response_status' => 1,
            'provider' => "Wallet",
        ]);
       
      $walletDeducted =  WalletHistory::create([
        'user_id' => $authUser->id,
        'payment_channel' => 'Wallet', 
        'price' => $this->checkTrans->amount,
        'transactionId' => $this->checkTrans->transaction_id,
        'status' => '1',
        'entry' => 'DR'
        ]);
       
       //send token to the user
       return $this->sendSuccess($this->checkTrans, "PaymentSource Successfully Loaded", Response::HTTP_OK);
      
    }
}
