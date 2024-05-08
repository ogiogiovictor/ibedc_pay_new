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
       


        ///////////////////////// CLOSING FCMB COMPLETE TRANSACTION /////////////////////////////////////////////////
        // if($request->provider == 'FCMB' && $payment->pay()['data']['transactionStatus'] == "Success"){   //$fcmbResponse->data->transactionStatus != "Success"
        //      return $this->checkSwitch($request->account_type, $request, $checkTrans, $payment->pay());
        // } else {
        //     return $this->sendError('Error Verifying Payments', "Error!", Response::HTTP_BAD_REQUEST);
        // }

        if($request->provider == 'FCMB') {
            $paymentResponse = $payment->pay();

            if (isset($paymentResponse['data']['transactionStatus']) && $paymentResponse['data']['transactionStatus'] == 'Success') {
                return $this->checkSwitch($request->account_type, $request, $checkTrans, $payment->pay());
            } else {
                return $this->sendError('Error Verifying FCMB Payments', "Error!", Response::HTTP_BAD_REQUEST);
            }
        }


        ///////////////////////// CLOSING WALLET COMPLETE TRANSACTION ///////////////////////////////////////////
        if($request->provider == 'Wallet') { 
            $paymentResponse = $payment->pay();
            $payload = $paymentResponse->getData(true)['message']; 
               if($payload == 'Error'){
                   return $paymentResponse->getData(true);
               } else {
                   $npayload =   $paymentResponse->getData(true)['payload'];
                  // return $this->checkSwitch($request->account_type, $request, $checkTrans, $npayload);
               }
   
         } else {
            return $this->sendError('Error Verifying Payments', "Error!", Response::HTTP_BAD_REQUEST);
         }


    /*
     
      if($request->provider == 'Polaris' && $payment->pay() && $payment->pay()['data']['status'] == 'successful'){

        return $this->checkSwitch($request->account_type, $request, $checkTrans, $payment->pay());

      } else if($request->provider == 'FCMB' && $payment->pay()['data']['transactionStatus'] == "Success"){   //$fcmbResponse->data->transactionStatus != "Success"

       // return $this->checkSwitch($request->account_type, $request, $checkTrans, $payment->pay());

      } else if($request->provider == 'Wallet') { 
         $paymentResponse = $payment->pay();
         $payload = $paymentResponse->getData(true)['message']; 
            if($payload == 'Error'){
                return $paymentResponse->getData(true);
            } else {
                $npayload =   $paymentResponse->getData(true)['payload'];
                return $this->checkSwitch($request->account_type, $request, $checkTrans, $npayload);
            }

      }else  {
        return $this->sendError('Invalid Payment Type', "Error!", Response::HTTP_BAD_REQUEST);
      }

        // if($payment->pay() && $payment->pay()['data']['status'] == 'successful') {

        //     switch($request->account_type){  
        //         case TransactionEnum::Postpaid()->value :
        //             return (new PostPaidService)->processService($checkTrans, $request, $payment->pay());
        //         case TransactionEnum::Prepaid()->value :
        //             return (new PrePaidService)->processService($checkTrans, $request, $payment->pay());
        //         default :
        //         return $this->sendError('Invalid Payment Type', "Error!", Response::HTTP_BAD_REQUEST);
        //     }
        // }

        */
        
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


    



}
