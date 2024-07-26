<?php

namespace App\Services;

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Models\Polaris\PolarisPaymentPayload;


class PolarisLogService extends BaseAPIController
{
    
    public function processLogs($transactionid, $meterno = null, $accountno = null, $payload)
    {

        $createAccount = PolarisPaymentPayload::create([
            'transaction_id' => $transactionid,
            'meter_no' => $meterno,
            'account_no' => $accountno,
            'payload' => $payload
        ]);


        // try {
        //     $createAccount = PolarisPaymentPayload::create([
        //         'transaction_id' => $transactionid,
        //         'meter_no' => $meterno,
        //         'account_no' => $accountno,
        //         'payload' => $payload
        //     ]);

        //     return response()->json(['message' => 'Log created successfully'], Response::HTTP_CREATED);
        // } catch (\Exception $e) {
        //     return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        // }
    }
}
