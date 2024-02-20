<?php

namespace App\Repositories\Payment;
use App\Interfaces\PayableInterface;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use App\Models\Transactions\PaymentTransactions;


class FcmbPaymentRepository extends BaseApiController implements PayableInterface
{
    public $checkTrans;

    public function __construct($checkTrans) {
        $this->checkTrans = $checkTrans;
    }


    public function pay()
    {
        $FCMB_LINK = env("FCMB_TEST_URL");
        $FCMB_MERCHANT_CODE = env("FCMB_TEST_MERCHANT_CODE");
        $FCMB_AUTHORIZATION = env("FCMB_TEST_AUTHORIZATION");

        $FULL_LINK = $FCMB_LINK."".$this->checkTrans->transaction_id."?sof=true";

        //https://paymentgatewaymiddleware.fcmb-azr-msase.p.azurewebsites.net/api/transactions/verify/93FA7FB22F64DFBE?sof=true

        $iresponse = Http::get($FULL_LINK, [
            'merchant_code' => $FCMB_MERCHANT_CODE, // flutterwave polaris
            "Authorization" => $FCMB_AUTHORIZATION
         ]);

         $fcmbResponse = $iresponse->json(); 

         if (!isset($fcmbResponse->data->transactionStatus) || ($fcmbResponse->data->transactionStatus != "Success")) {
            return $this->sendError('Invalid Payment', "Error Verifying Payment", Response::HTTP_BAD_REQUEST);
        }

        if ($fcmbResponse->data->transactionStatus != "Success") {
            $update = PaymentTransactions::where("transaction_id", $checkRef->transaction_id)->update([
                'providerRef' => $flutterResponse['data']['flwref'],
                'Descript' => $flutterResponse['data']['status'],
                'response_status' => 1,
            ]);
        }

        return $fcmbResponse;
    }
}
