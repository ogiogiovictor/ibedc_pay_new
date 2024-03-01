<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet\WalletUser;
use App\Models\Polaris\Payments;
use App\Models\Wallet\WalletHistory;
use App\Models\VirtualAccount;


class PolarisPaymentNotification extends BaseAPIController
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
    public function store(Request $request)
    {
    
       try {   

        Log::info('Polaris Notification Request', $request->all());
        $checkRef = Payments::where('request_ref', $request['request_ref'])->first();

       if(!$checkRef && $request['details']['status'] == "Successful"){  // check for the transaction reference updated to 0

          $payment =   Payments::create([
            'request_ref' => $request['request_ref'],
            'request_type' => $request['request_type'],
            'requester' => $request['requester'],
            'transaction_type' => $request['details']['transaction_type'],
            'amount' => $request['details']['data']['TransactionAmount'],
            'status' => $request['details']['status'],
            'provider' =>  $request['details']['provider'],
            'transaction_ref' =>  $request['details']['transaction_ref'],
            'VirtualAccount'  =>  $request['details']['data']['VirtualAccount'],
            'VirtualAccountName'  =>  $request['details']['data']['VirtualAccountName'],
            'Narration'  =>  $request['details']['data']['Narration'],
            'SenderAccountNumber' =>  $request['details']['data']['SenderAccountNumber'], 
            'SenderAccountName' =>  $request['details']['data']['SenderAccountName'],  
            'SenderBankName' =>  $request['details']['data']['SenderBankName'],
            'account_number' =>  $request['details']['data']['account_number'],
            'transaction_date' =>  $request['details']['meta']['transaction_date'],
            'customer_ref' =>  $request['details']['customer_ref'],
            'customer_firstname'  =>  $request['details']['customer_firstname'], 
            'customer_surname' =>  $request['details']['customer_surname'], 
            'customer_email' =>  $request['details']['customer_email'], 
            'customer_mobile_no' => $request['details']['customer_mobile_no'], 
            'Hash' => $request['details']['data']['Hash'], 
            'used' => 0
            ]);

            $user_id = VirtualAccount::where("account_no", $request['details']['data']['VirtualAccount'])->value('user_id');

            if($user_id){

                $user =  WalletHistory::create([
                    'user_id' => $user_id,
                    'payment_channel' => 'Wallet', 
                    'price' => $request['details']['data']['TransactionAmount'],
                    'transactionId' => $request['request_ref'],
                    'status' => '1',
                    'entry' => 'CR'
                    ]);
    
                    //$walletAmount = (int) WalletUser::where('user_id', $user_id)->value('wallet_amount');
                    $walletAmount = (int) WalletUser::where('user_id', $user_id)->first();

                    //update the wallet
                    $walletUpdate = WalletUser::where('user_id', $user_id)->update([
                        'wallet_amount' => $walletAmount->wallet_amount + $request['details']['data']['TransactionAmount']
                    ]);
                    
                    //Update the payment after the wallet has been topup
                    Payments::where("request_ref", $request['request_ref'])->update([
                        'used' => 1
                    ]);
            }
            
            return $payment;

            //Send Email  Notification for Payment Using Job
			Mail::to($request['details']['customer_email'])->queue(new TransactionNotificationMail($payment));

        } else {
            return $this->sendError('ERROR', 'Reference Already Exist in Database', Response::HTTP_UNAUTHORIZED);
        }

       }catch(\Exception $e){

            Log::error('Error Log from Polaris', ['error_message' => $e->getMessage()]);
            return $e->getMessage();
            
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
