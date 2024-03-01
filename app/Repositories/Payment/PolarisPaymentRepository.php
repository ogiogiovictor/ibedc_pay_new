<?php

namespace App\Repositories\Payment;
use App\Interfaces\PayableInterface;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use App\Models\Transactions\PaymentTransactions;

class PolarisPaymentRepository extends BaseApiController implements PayableInterface
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

         $polarisKey = env("FLUTTER_POLARIS_KEY");
         $polarisURL = env("FLUTTER_WAVE_URL");

         $iresponse = Http::post($polarisURL, [
            'SECKEY' => $polarisKey, // flutterwave polaris
            "txref" => $this->checkTrans->transaction_id
         ]);

         $flutterResponse = $iresponse->json(); 

         if (!isset($flutterResponse['status']) || ($flutterResponse['status'] != "success" && (!isset($flutterResponse['data']['status']) || $flutterResponse['data']['status'] != 'successful'))) {
            return $this->sendError('Invalid Payment', "Error Verifying Payment", Response::HTTP_BAD_REQUEST);
        }

        if ($flutterResponse['status'] == "success" && $flutterResponse['data']['status'] == 'successful') {
            $update = PaymentTransactions::where("transaction_id", $this->checkTrans->transaction_id)->update([
                'providerRef' => $flutterResponse['data']['flwref'],
                'Descript' => $flutterResponse['data']['status'],
                'response_status' => 1,
                'provider' => $this->request->provider,
            ]);
        }

        return $flutterResponse;
    }
}
