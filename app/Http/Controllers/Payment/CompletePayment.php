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
        $payment = $paymentFactory->initializePayment($request->provider, $checkTrans);
        $payment->pay();

       // return $payment->pay()['data']['status'];
      //return $request->account_type;

        if($payment->pay() && $payment->pay()['data']['status'] == 'successful') {

            switch($request->account_type){  
                case TransactionEnum::Postpaid()->value :
                    return (new PostPaidService)->processService($checkTrans, $request, $payment->pay());
                case TransactionEnum::Prepaid()->value :
                    return (new PrePaidService)->processService($checkTrans, $request, $payment->pay());
                default :
                return $this->sendError('Invalid Payment Type', "Error!", Response::HTTP_BAD_REQUEST);
            }
        }
        
    }


    



}
