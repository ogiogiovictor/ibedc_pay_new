<?php

namespace App\Http\Controllers\History;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Models\EMS\ZoneBills;
use App\Models\EMS\ZonePayments;
use App\Models\EMS\ZoneCustomers;
use App\Models\EMS\BusinessUnit;
use App\Enums\TransactionEnum;
use Illuminate\Support\Facades\Http;


class CustomerBillHistory extends BaseAPIController
{
    public function customerBills(Request $request) {

        if(!$request->type || !$request->customer_number){
            return $this->sendError('Error Verifying Bills', "Error!", Response::HTTP_BAD_REQUEST);
        }

        if($request->type == TransactionEnum::Postpaid()->value ){

            $checkifExist = ZoneBills::where("AccountNo", $request->customer_number)->orderby("Billdate", "desc")->paginate(10);

            if($checkifExist){

                return $this->sendSuccess( [
                    'payload' => $checkifExist,
                    'message' => 'Customer Bills Successfully Loaded',
                ], Response::HTTP_OK);

            } else {
                return $this->sendError('No Bill History', "Error!", Response::HTTP_BAD_REQUEST);
            }


        } else {
            return $this->sendError('Invalid Parameter Provided', "Error!", Response::HTTP_BAD_REQUEST);
        }

    }



    public function getCustomers(Request $request) {

       $businesshub = $request->query('business_hub');

        $bill = BusinessUnit::where("Name", strtoupper($businesshub))->first();

        if (!$bill) {
            return $this->sendError("Invalid business hub: '{$businesshub}'", Response::HTTP_NOT_FOUND);
        }

        $customers = ZoneCustomers::where('BUID', $bill->BUID)->orderby("ArrearsBalance", "desc")->paginate(20);

        //ZoneBills :- loop through each ZoneCustomers and return hte lastpayment and lastdate so you need to filter
         // Loop through each customer and append lastpayment and lastdate from ZoneBill
        $customers->getCollection()->transform(function ($customer) {
            $latestPayment = ZonePayments::where('AccountNo', $customer->AccountNo)
                ->orderByDesc('PayDate') // Assuming PaymentDate is the column for latest
                ->first();

            $customer->lastpaymentday = $latestPayment->PayDate ?? null;
            $customer->lastpayment = $latestPayment->Payments ?? null;

             // Get minimum purchase via external API
             $customer->customerArrears = $this->getMinimumPurchase($customer->AccountNo);

            return $customer;
        });

        return $this->sendSuccess([
            'payload' => $customers,
            'message' => 'Customer Successfully Loaded',
        ], Response::HTTP_OK);

    }


    private function getMinimumPurchase($accountNo)
    {
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
            ])->post("https://middleware3.ibedc.com/api/v1/verifymeter", [
                'meter_number' => $accountNo,
                'vendtype' => 'Postpaid',
            ]);

            $data = $response->json();

            return $data['data']['customerArrears'] ?? 0;
        } catch (\Exception $e) {
            \Log::error("Minimum Purchase API error for {$accountNo}: " . $e->getMessage());
            return 0;
        }
    }



}
