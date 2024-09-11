<?php

namespace App\Factory;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Repositories\Payment\PolarisPaymentRepository;
use App\Repositories\Payment\FcmbPaymentRepository;
use App\Repositories\Payment\WalletPaymentRepository;
use App\Enums\PaymentEnum;

class  PaymentFactory extends BaseAPIController {

    public function initializePayment($type, $checkTrans, $request)
    {
        if($type == PaymentEnum::Polaris()->value){

            return new PolarisPaymentRepository($type, $checkTrans, $request);

        } else if($type == PaymentEnum::FCMB()->value){

            
            return new NewFCMBPaymentRepository($type, $checkTrans, $request);

           // return new FcmbPaymentRepository($type, $checkTrans, $request);

        } else if($type == PaymentEnum::Wallet()->value){

            return new WalletPaymentRepository($type, $checkTrans, $request);

        } else {
            return $this->sendError('Unsupported Payment Method', 'Error!', Response::HTTP_UNAUTHORIZED);
        }
    }
}