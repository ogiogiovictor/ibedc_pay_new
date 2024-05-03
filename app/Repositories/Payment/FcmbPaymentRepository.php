<?php

namespace App\Repositories\Payment;
use App\Interfaces\PayableInterface;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use App\Models\Transactions\PaymentTransactions;
use Illuminate\Support\Facades\Log;


class FcmbPaymentRepository extends BaseApiController implements PayableInterface
{
    public $checkTrans;
    public $type;
    public $request;

    public function __construct($type, $checkTrans, $request) {
        $this->checkTrans = $checkTrans;
        $this->type = $type;
        $this->request = $request;
    }


    public function pay()
    {
        $FCMB_LINK = env("FCMB_TEST_URL");
        $FCMB_MERCHANT_CODE = env("FCMB_TEST_MERCHANT_CODE");
        $FCMB_AUTHORIZATION = env("FCMB_TEST_AUTHORIZATION");

        // $FULL_LINK = $FCMB_LINK."".$this->request->payRef."?sof=true";

        // $FCMB_LINK = env("FCMB_LIVE_URL");
        // $FCMB_MERCHANT_CODE = env("FCMB_LIVE_MERCHANT_CODE");
        // $FCMB_AUTHORIZATION = env("FCMB_LIVE_AUTHORIZATION");

        //Live Key
        //$FULL_LINK = $FCMB_LINK."".$this->request->payRef."?subscription-key=1ac401806abe49efb0dd98f2489acca9";

        //Test Key
        $FULL_LINK = $FCMB_LINK."".$this->request->payRef;

        //LIVE LINK
        //https://liveapi.fcmb.com/paymentgatewaymiddleware-callback-prod/api/transactions/verify/{transRef}?subscription-key=1ac401806abe49efb0dd98f2489acca9

        //TEST LINK
        //https://paymentgatewaymiddleware.fcmb-azr-msase.p.azurewebsites.net/api/transactions/verify/93FA7FB22F64DFBE?sof=true
        //https://paymentgatewaymiddleware.fcmb-azr-msase.p.azurewebsites.net/api/transactions/verify/16AB8E72A6F6508E?sof=true
        //https://paymentgatewaymiddleware.fcmb-azr-msase.p.azurewebsites.net/api/transactions/verify/

        //  $iresponse = Http::get($FULL_LINK, [
        //     'merchant_code' => $FCMB_MERCHANT_CODE, // flutterwave polaris
        //     "Authorization" => $FCMB_AUTHORIZATION
        //  ]);

            $iresponse = Http::withHeaders([
                'merchant_code' => $FCMB_MERCHANT_CODE, // flutterwave polaris
                "Authorization" => $FCMB_AUTHORIZATION
            ])->get($FULL_LINK);

         if (!$iresponse->successful()) {
            // Handle unsuccessful response
            return $this->sendError('Error Verifying Payment', $iresponse->status(), Response::HTTP_BAD_REQUEST);
         }    


         
        if($iresponse['code'] == 57) {
            return $this->sendError($iresponse['description'], "Error Verifying Payment", Response::HTTP_BAD_REQUEST);
        };

         $fcmbResponse = $iresponse->json(); 

        \Log::info('FCMB Response: ' . json_encode($fcmbResponse));

         if (!isset($fcmbResponse['data']['transactionStatus']) && ($fcmbResponse['data']['transactionStatus'] != "Success")) {
            return $this->sendError('Invalid Payment', "Error Verifying Payment", Response::HTTP_BAD_REQUEST);
        }

        if ($fcmbResponse['data']['transactionStatus'] == "Success") {
            $update = PaymentTransactions::where("transaction_id", $this->checkTrans->transaction_id)->update([
                'providerRef' => $fcmbResponse['data']['transactionRef'],
                'Descript' => $fcmbResponse['data']['transactionStatus'],
                'response_status' => 1,
                'provider' => $this->request->provider,
            ]);

          return $fcmbResponse;

        } else {
            return $this->sendError($fcmbResponse, "Error Verifying Payment", Response::HTTP_BAD_REQUEST);
        }

      //  \Log::info('FCMB Success Response: ' . $fcmbResponse->data->transactionStatus);

        
    }
}
