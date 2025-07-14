<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\VendingRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Interfaces\TransactionRepositoryInterface;
use Illuminate\Support\Facades\Http;
use App\Factory\PaymentFactory;
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
use Mail;
use App\Mail\PrePaidPaymentMail;
use App\Models\ECMI\EcmiPayments;

class CompletePayment extends BaseAPIController
{

    private TransactionRepositoryInterface $transaction;
    public $polarisKey;
    public $fcmbKey;
    public $polarisURL;


    public function __construct(TransactionRepositoryInterface $transaction) {

        $this->transaction = $transaction;
       
    }



    public function CompletePayment(CompletePaymentRequest $request){

        $checkTrans = $this->transaction->show($request->transacion_id);

        //Check for undefined and Null paymentRef
        if($request->payRef == 'undefined' || $request->payRef == 'NULL'){
            return $this->sendError('Invalid Transaction', 'Error!', Response::HTTP_UNAUTHORIZED);
        }

        if(!$checkTrans){
            return $this->sendError('The Transaction does not exist', "Error!", Response::HTTP_BAD_REQUEST);
        }

        if($checkTrans->status == "success"){
            return $this->sendError($checkTrans, "Success - Transation Exist", Response::HTTP_OK);
        }

        // Open & Closed Principle // Extend the functionality of a system by adding new code instead of changing the existing ones
        //Factory Pattern is Used Here(Open & Closed Principle)
        $paymentFactory = new PaymentFactory();
        $payment = $paymentFactory->initializePayment($request->provider, $checkTrans, $request);
        $payment->pay();
       // $paymentResponse = $payment->pay();

        ///////////////////////// CLOSING POLARIS COMPLETE TRANSACTION //////////////////////////////////////////////
        if ($request->provider == 'Polaris') {
             $paymentResponse = $payment->pay(); // Get the response object
            //$content = $paymentResponse->getContent(); // Get the content of the response
            //$paymentResponseArray = json_decode($content, true); // Decode the JSON content to an array
            //isset($payment->pay()) && $payment->pay()['data']['status'] == 'successful'
            
            if (isset($paymentResponse['data']['status']) && $paymentResponse['data']['status'] == 'successful') {
                return $this->checkSwitch($request->account_type, $request, $checkTrans, $payment->pay());
            } else {
                return $this->sendError('Error Verifying Polaris Payments', "Error!", Response::HTTP_BAD_REQUEST);
            }
        }
       


        if($request->provider == 'FCMB') {

            //return $this->sendError('Error Please use Polaris', "Error!", Response::HTTP_BAD_REQUEST);

            $paymentResponse = $payment->pay();

            if (isset($paymentResponse['data']['status']) && $paymentResponse['data']['status'] == 'successful') {
                return $this->checkSwitch($request->account_type, $request, $checkTrans, $payment->pay());
            } else {
                return $this->sendError('Error Verifying FCMB Payments', "Error!", Response::HTTP_BAD_REQUEST);
            }
        }


        ///////////////////////// CLOSING WALLET COMPLETE TRANSACTION ///////////////////////////////////////////
        if($request->provider == 'Wallet') { 
           
        
        //    $authUser = Auth::user();
 
        //     // Ensure the user has a wallet
        //   if (!$authUser->wallet) {
        //       return $this->sendError('User does not have a wallet.', "Error", Response::HTTP_BAD_REQUEST);
        //    }

        //    // Lock the wallet record for the user
        //    $wallet = WalletUser::where('user_id', $authUser->id)->lockForUpdate()->first();
    
        //    // Check if the wallet has enough balance
        //    $walletBalance = (float) $wallet->wallet_amount;
        //    $transactionAmount = (float) abs($request->amount);
    
    
        //    if ($transactionAmount > $walletBalance) {
        //     //return "Insufficient wallet balance";
        //     return $this->sendError('Insufficient wallet balance. Please fund your wallet.', "Error", Response::HTTP_BAD_REQUEST);
        //    }

        //     // Check if wallet history entry already exists for this transaction ID
        //     $existingWalletHistory = WalletHistory::where('transactionId', $request->transacion_id)->first();

        //     if ($existingWalletHistory) {
        //         //return 'Wallet history entry already exists';
        //         return $this->sendError('Wallet history entry already exists for this transaction', "Error", Response::HTTP_BAD_REQUEST);
        //     }

            
        //     $transactionAmount = abs($request->amount);
        //     $authUser->wallet->decrement('wallet_amount', $transactionAmount);
 
        //    $chekUpdate =  PaymentTransactions::where("transaction_id", $request->transaction_id)->update([
        //      'providerRef' => StringHelper::generateUUIDReference(),
        //      'Descript' => "Wallet Fund Sucessfully Deducted",
        //      'response_status' => 1,
        //      'provider' => "Wallet",
        //     ]);

        //      // Create wallet history entry
        //    WalletHistory::create([
        //     'user_id' => $authUser->id,
        //     'payment_channel' => 'Wallet',
        //     'price' => $request->amount,
        //     'transactionId' => $request->transaction_id,
        //     'status' => 'successful',
        //     'entry' => 'DR', // DR = Debit Record
        //     ]);

            // if( $chekUpdate ) {
            //     return $this->checkSwitch($request->account_type, $request, $checkTrans, $npayload);
            // }else {
            //     return $this->sendError('Error Processing Wallet Payment', "Error!", Response::HTTP_BAD_REQUEST);
            // }


    
           
         //  return $paymentResponse = $payment->pay();


            // $payload = $paymentResponse->getData(true)['message']; 

            // if($payload == 'Error'){
            //     return $paymentResponse->getData(true);
            // }


            // $npayload =   $paymentResponse->getData(true)['payload'];

            // if($payload == 'Success') {
            //     return $this->checkSwitch($request->account_type, $request, $checkTrans, $npayload);
            //    } else {
            //     return $npayload;
            //    }
   
         } else {
            return $this->sendError('Error Verifying Payments', "Error!", Response::HTTP_BAD_REQUEST);
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


    public function TokenNotifications(){

       return $checkTrans = $this->transaction->usernotification(Auth::user()->id);

    }


    public function retryPayment(Request $request)
        {
            $checkTransaction = PaymentTransactions::where([
                'transaction_id' => $request->transaction_id,
                "status" => 'Processing'
            ])->first();

            if (!$checkTransaction) {
                return $this->sendError('Invalid transaction.', 'Error!', Response::HTTP_BAD_REQUEST);
            }

            $prepaidTransaction = PaymentTransactions::whereNull('receiptno')
                ->where('account_type', 'Prepaid')
                ->where('status', 'processing')
                ->whereNotNull('providerRef')
                ->where('transaction_id', $request->transaction_id)
                ->first();

            if (!$prepaidTransaction) {
                return $this->sendError('Transaction not found or not valid for retry.', 'Error!', Response::HTTP_NOT_FOUND);
            }

            if (!is_null($prepaidTransaction->receiptno)) {
                PaymentTransactions::where("transaction_id", $prepaidTransaction->transaction_id)->update([
                    'status' => 'success',
                ]);

                $emailData = [
                    'token' => $prepaidTransaction->receiptno,
                    'meterno' => $prepaidTransaction->meter_no,
                    'amount' => $prepaidTransaction->amount,
                    "custname" => $prepaidTransaction->customer_name,
                    "custphoneno" => $prepaidTransaction->phone,
                    "payreference" => $prepaidTransaction->transaction_id,
                ];

                Mail::to($prepaidTransaction->email)->send(new PrePaidPaymentMail($emailData));

                return $this->sendSuccess($prepaidTransaction, "Token Successful", Response::HTTP_OK);
            } else {
                $baseUrl = env('MIDDLEWARE_URL');
                $addCustomerUrl = $baseUrl . 'vendelect';

                $data = [
                    'meterno' => $prepaidTransaction->meter_no,
                    'vendtype' => $prepaidTransaction->account_type,
                    'amount' => $prepaidTransaction->amount,
                    'provider' => "IBEDC",
                    "custname" => $prepaidTransaction->customer_name,
                    "businesshub" => $prepaidTransaction->BUID,
                    "custphoneno" => $prepaidTransaction->phone,
                    "payreference" => $prepaidTransaction->transaction_id,
                    "colagentid" => "IB001",
                ];

                $response = Http::withoutVerifying()->withHeaders([
                    'Authorization' => env('MIDDLEWARE_TOKEN'),
                ])->post($addCustomerUrl, $data);

                $newResponse = $response->json();

                // return $newResponse;

                if (isset($newResponse['status']) && $newResponse['status'] == "true") {
                    PaymentTransactions::where("transaction_id", $prepaidTransaction->transaction_id)->update([
                        'status' => 'success',
                        'receiptno' => isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'],
                        'Descript' => "Token Successfuly Sent",
                        'units' => $newResponse['Units'] ?? $newResponse['data']['Units'] ?? '',
                        'minimumPurchase' => $newResponse['customer']['minimumPurchase'] ?? '',
                        'tariffcode' => $newResponse['customer']['tariffcode'] ?? '',
                        'customerArrears' => $newResponse['customer']['customerArrears'] ?? '',
                        'tariff' => $newResponse['customer']['tariff'] ?? '',
                        'serviceBand' => $newResponse['customer']['serviceBand'] ?? '',
                        'feederName' => $newResponse['customer']['feederName'] ?? '',
                        'dssName' => $newResponse['customer']['dssName'] ?? '',
                        'udertaking' => $newResponse['customer']['undertaking'] ?? '',
                        'VAT' => EcmiPayments::where("transref", $newResponse['data']['transactionReference'])->value('VAT'),
                        'costOfUnits' => EcmiPayments::where("transref", $newResponse['data']['transactionReference'])->value('CostOfUnits'),
                    ]);

                    $token = $newResponse['recieptNumber'] ?? $newResponse['data']['recieptNumber'] ?? '';

                    $emailData = [
                        'token' => $token,
                        'meterno' => $prepaidTransaction->meter_no,
                        'amount' => $prepaidTransaction->amount,
                        "custname" => $prepaidTransaction->customer_name,
                        "custphoneno" => $prepaidTransaction->phone,
                        "payreference" => $prepaidTransaction->transaction_id,
                    ];

                    $user = Auth::user();

                    Mail::to($user->email)->cc($prepaidTransaction->email)->send(new PrePaidPaymentMail($emailData));

                    return $this->sendSuccess($prepaidTransaction, "Token Successful", Response::HTTP_OK);
                } else {
                    // Handle middleware vending failure
                    return $this->sendError(
                        $newResponse['message'] ?? 'Unable to vend token at the moment.',
                        'Vending Failed',
                        Response::HTTP_BAD_GATEWAY
                    );
                }
            }
        }



    // public function retryPayment(Request $request) {

    //     $checkTransaction = PaymentTransactions::where(['transaction_id' => $request->transaction_id, "status" => 'Processing'])->first();

    //     if(!$checkTransaction) {
    //         return $this->sendError('Error Transaction', "Error!", Response::HTTP_BAD_REQUEST);
    //     }

        
    //     $prepaidTransaction = PaymentTransactions::whereNull('receiptno')
    //     ->where('account_type', 'Prepaid')
    //     ->where('status', 'processing')  
    //     ->whereNotNull('providerRef')
    //     ->where('transaction_id', $request->transaction_id)->first();


    //     if($prepaidTransaction && !is_null($prepaidTransaction->receiptno)) {

    //         $update = PaymentTransactions::where("transaction_id", $prepaidTransaction->transaction_id)->update([
    //             'status' => 'success',
    //         ]);

    //         $emailData = [
    //             'token' => $prepaidTransaction->receiptno,
    //             'meterno' => $prepaidTransaction->meter_no,
    //             'amount' => $prepaidTransaction->amount,
    //             "custname" => $prepaidTransaction->customer_name,
    //             "custphoneno" => $prepaidTransaction->phone,
    //             "payreference" => $prepaidTransaction->transaction_id,
    //         ];

    //         Mail::to($prepaidTransaction->email)->send(new PrePaidPaymentMail($emailData));

    //         return $this->sendSuccess($prepaidTransaction, "Token Successful", Response::HTTP_OK);
    //     } else {

    //         $baseUrl = env('MIDDLEWARE_URL');
    //         $addCustomerUrl = $baseUrl. 'vendelect';

    //         $data = [
    //             'meterno' => $prepaidTransaction->meter_no,
    //             'vendtype' => $prepaidTransaction->account_type,
    //             'amount' => $prepaidTransaction->amount, 
    //             'provider' => "IBEDC",
    //             "custname" => $prepaidTransaction->customer_name,
    //             "businesshub" => $prepaidTransaction->BUID,
    //             "custphoneno" => $prepaidTransaction->phone,
    //             "payreference" => $prepaidTransaction->transaction_id,
    //             "colagentid" => "IB001",
                                 
    //         ];

    //         $response = Http::withoutVerifying()->withHeaders([
    //             'Authorization' => env('MIDDLEWARE_TOKEN'), // 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
    //         ])->post($addCustomerUrl, $data);
    
    //         $newResponse =  $response->json();

    //         if($newResponse['status'] == "true"){      
                        
    //             $paymentData[] = $data;
              
    //              $update = PaymentTransactions::where("transaction_id", $prepaidTransaction->transaction_id)->update([
    //                 // 'status' => $newResponse['status'] == "true" ?  'success' : 'failed', //"resp": "00",
    //                  'status' => 'success',
    //                  'receiptno' =>   isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'],
    //                  'Descript' =>  isset($newResponse['message']) ? $newResponse['message']."-".$newResponse['transactionReference'] : $newResponse['transaction_status']."-".$newResponse['transactionReference'],
    //                 'units' => isset($newResponse['Units']) ? $newResponse['Units'] : $newResponse['data']['Units'], 
    //                 'minimumPurchase' => isset($newResponse['customer']['minimumPurchase']) ? $newResponse['customer']['minimumPurchase'] : '',
    //                 'tariffcode'  => isset($newResponse['customer']['tariffcode']) ? $newResponse['customer']['tariffcode'] : '',
    //                 'customerArrears' => isset($newResponse['customer']['customerArrears']) ? $newResponse['customer']['customerArrears'] : '',
    //                 'tariff' => isset($newResponse['customer']['tariff']) ? $newResponse['customer']['tariff'] : '',
    //                 'serviceBand' => isset($newResponse['customer']['serviceBand']) ? $newResponse['customer']['serviceBand'] : '',
    //                 'feederName' => isset($newResponse['customer']['feederName']) ? $newResponse['customer']['feederName'] : '',
    //                 'dssName' => isset($newResponse['customer']['dssName']) ? $newResponse['customer']['dssName'] : '',
    //                 'udertaking' => isset($newResponse['customer']['undertaking']) ? $newResponse['customer']['undertaking'] : '',
    //                 'VAT' =>  EcmiPayments::where("transref", $newResponse['transactionReference'])->value('VAT'),
    //                 'costOfUnits' => EcmiPayments::where("transref", $newResponse['transactionReference'])->value('CostOfUnits'),
    //              ]);

    //              $token =  isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'];
              

    //              $emailData = [
    //                 'token' => $token,
    //                 'meterno' => $prepaidTransaction->meter_no,
    //                 'amount' => $prepaidTransaction->amount,
    //                 "custname" => $prepaidTransaction->customer_name,
    //                 "custphoneno" => $prepaidTransaction->phone,
    //                 "payreference" => $prepaidTransaction->transaction_id,
    //             ];

    //             $user = Auth::user();

    //              Mail::to($user->email)->cc($prepaidTransaction->email)->send(new PrePaidPaymentMail($emailData));

    //              return $this->sendSuccess($prepaidTransaction, "Token Successful", Response::HTTP_OK);

    //         } else {

    //         }
            
    //     }




    // }


    



}
