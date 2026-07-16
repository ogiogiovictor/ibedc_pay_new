<?php

namespace App\Http\Controllers\Middleware;

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
use App\Models\VirtualAccountTrasactions;
use App\Models\NAC\MiddlewareWarehouse;
use App\Models\ECMI\EcmiCustomers;
use App\Models\NAC\UBVSGetCustomers;


class TransactionHistory extends BaseAPIController
{
    public function TransactionHistory(Request $request){

        //Get user details from Auth
        $user = Auth::user();

        if (!$user->meter_no_primary) {
            return $this->sendError('No Primary Meter', "Error!", Response::HTTP_BAD_REQUEST);
        }

        $getTransaction = MiddlewareWarehouse::where('AccountID', $user->meter_no_primary)->where('transaction_status', 1)
                            ->orderBy('TimeStamp', 'desc')
                            ->paginate(5);
        
        $getTransaction->getCollection()->transform(function ($item) {
            return [
                'timestamp'    => $item->TimeStamp,
                'reference'    => $item->TXMessage,
                'meter_number' => $item->MeterNumber,
                'amount'       => number_format($item->GrossCollectionAmount, 2),
                'energy_kwh'   => $item->EnergyConsumed,
                'paymentID'    => $item->BillingID,
                'status'       => $item->transaction_status == 1 ? 'Successful' : 'Failed',
                'agent'    => $item->SAgentName,
                'date'         => \Carbon\Carbon::parse($item->TimeStamp)->toDateTimeString(),
                'BusinessHub'    => $item->BusinessHub ?? null,
                'ServiceUnit'    => $item->ServiceUnit ?? null,
                'paytype'    => $item->paytype ?? null,
                'settlement_reference'    => $item->settlement_reference ?? null,
                'custName'    => EcmiCustomers::where('MeterNo', $item->MeterNumber)->value('Surname'). ' '. EcmiCustomers::where('MeterNo', $item->MeterNumber)->value('OtherNames'),  // $item->custName ?? null,
                'dss'    => $item->dss ?? null,
                'AccountNo'    => $item->AccountNo ?? null,
                'feederName'    => $item->feederName ?? null,
                 'Address'    =>  EcmiCustomers::where('MeterNo', $item->MeterNumber)->value('Address') ?? null,
                'serviceBand'   =>  $item->serviceBand ?? null,
                'token'   =>  $item->token ?? null,
            ];
        });

        if(!$getTransaction){
           return $this->sendError('The Transaction does not exist', "Error!", Response::HTTP_BAD_REQUEST);
        }

        return $this->sendSuccess($getTransaction, 'Transaction history retrieved successfully', Response::HTTP_OK);
    }


    public function MoreTransactionHistory(Request $request) {

        //Get user details from Auth.
        $user = Auth::user();

        //Base on the user details get all the meters associated with the user in an array.
        $getallMeters = PaymentTransactions::where('user_id', $user->id)
                            ->pluck('account_number', 'meter_no')
                            ->toArray();

        //Use the array to get all transactions associated with the meters.
        $getallTransactions = MiddlewareWarehouse::whereIn('AccountNo', $getallMeters)
                                ->where('transaction_status', 1)
                                ->orderBy('TimeStamp', 'desc')
                                ->paginate(10);

         $getallTransactions->getCollection()->transform(function ($item) {
            return [
                'timestamp'    => $item->TimeStamp,
                'reference'    => $item->TXMessage,
                'meter_number' => $item->MeterNumber,
                'amount'       => number_format($item->GrossCollectionAmount, 2),
                'energy_kwh'   => $item->EnergyConsumed,
                'paymentID'    => $item->BillingID,
                'status'       => $item->transaction_status == 1 ? 'Successful' : 'Failed',
                'agent'    => $item->SAgentName,
                'date'         => \Carbon\Carbon::parse($item->TimeStamp)->toDateTimeString(),
                'BusinessHub'    => $item->BusinessHub ?? null,
                'ServiceUnit'    => $item->ServiceUnit ?? null,
                'paytype'    => $item->paytype ?? null,
                'settlement_reference'    => $item->settlement_reference ?? null,
                'custName'    => $item->custName ?? null,
                'dss'    => $item->dss ?? null,
                'AccountNo'    => $item->AccountNo ?? null,
                'feederName'    => $item->feederName ?? null,
                 'Address'    =>  null,
                'serviceBand'   =>  $item->serviceBand ?? null,
                 'token'   =>  $item->token ?? null,
            ];
        });

        //$getallTransactions = $this->formatResponse($getallTransactions);

        //Return the transactions.
        return $this->sendSuccess($getallTransactions, 'Transaction history retrieved successfully', Response::HTTP_OK);
    }

    public function getAnyTransaction(Request $request) {

        //check the the user authority is admin, dtm, billing, region

        $user = Auth::user();

        //check the the user authority is admin, dtm, billing, region
        if ($user->authority !== 'admin' && $user->authority !== 'dtm' && $user->authority !== 'billing' && $user->authority !== 'region') {
            return $this->sendError('Unauthorized access', 'Error', Response::HTTP_UNAUTHORIZED);
        }

        $request->validate([
            'meter_no' => 'required|string',
        ]);

        //Use the array to get all transactions associated with the meters.
        $getTransactions = MiddlewareWarehouse::where('AccountNo', $request->meter_no)->OrWhere('MeterNumber', $request->meter_no)
                                 ->where('transaction_status', 1)
                                ->orderBy('TimeStamp', 'desc')
                                ->paginate(5);

       $getTransaction->getCollection()->transform(function ($item) {
            return [
                'timestamp'    => $item->TimeStamp,
                'reference'    => $item->TXMessage,
                'meter_number' => $item->MeterNumber,
                'amount'       => number_format($item->GrossCollectionAmount, 2),
                'energy_kwh'   => $item->EnergyConsumed,
                'paymentID'    => $item->BillingID,
                'status'       => $item->transaction_status == 1 ? 'Successful' : 'Failed',
                'agent'    => $item->SAgentName,
                'date'         => \Carbon\Carbon::parse($item->TimeStamp)->toDateTimeString(),
                'BusinessHub'    => $item->BusinessHub ?? null,
                'ServiceUnit'    => $item->ServiceUnit ?? null,
                'paytype'    => $item->paytype ?? null,
                'settlement_reference'    => $item->settlement_reference ?? null,
                'custName'    => $item->CustName ?? null,
                'Address'    =>  null,
                'AccountNo'    => $item->AccountNo ?? null,
                'dss'    => $item->dss ?? null,
                'feederName'    => $item->feederName ?? null,
                'serviceBand'   =>  $item->serviceBand ?? null,
            ];
        });

        //Return the transactions.
        return $this->sendSuccess($getTransactions, 'Transaction history retrieved successfully', Response::HTTP_OK);
    }



    private function formatResponse($getTransaction) {
       return  $getTransaction->getCollection()->transform(function ($item) {
            return [
                'timestamp'    => $item->TimeStamp,
                'reference'    => $item->TXMessage,
                'meter_number' => $item->MeterNumber,
                'amount'       => number_format($item->GrossCollectionAmount, 2),
                'energy_kwh'   => $item->EnergyConsumed,
                'paymentID'    => $item->BillingID,
                'status'       => $item->transaction_status == 1 ? 'Successful' : 'Failed',
                'agent'    => $item->SAgentName,
                'date'         => \Carbon\Carbon::parse($item->TimeStamp)->toDateTimeString(),
                'BusinessHub'    => $item->BusinessHub ?? null,
                'ServiceUnit'    => $item->ServiceUnit ?? null,
                'paytype'    => $item->paytype ?? null,
                'settlement_reference'    => $item->settlement_reference ?? null,
                'custName'    => $item->CustName ?? null,
                'dss'    => $item->dss ?? null,
                'AccountNo'    => $item->AccountNo ?? null,
                'feederName'    => $item->feederName ?? null,
                'Address'    =>  null,
                'serviceBand'   =>  $item->serviceBand ?? null,
                 'token'   =>  $item->token ?? null,
            ];
        });
    }


    public function getUBVSCustomerHistory(Request $request) {
        $user = Auth::user();

        if (!$user->meter_no_primary) {
            return $this->sendError('No Primary Meter', "Error!", Response::HTTP_BAD_REQUEST);
        }

        $getTransaction = UBVSGetCustomers::where('account_no', $user->meter_no_primary)
                            ->orWhere('meter_number', $user->meter_no_primary)
                            ->orderBy('timestamp', 'desc')
                            ->paginate(5);

                            
        $getTransaction->getCollection()->transform(function ($item) {
            return [
                'timestamp'    => $item->timestamp,
                'reference'    => $item->payment_reference,
                'meter_number' => $item->meter_number,
                'amount'       => number_format($item->amount_tendered, 2),
                'energy_kwh'   => $item->energy_consumed,
                'cost_of_units'    => $item->cost_of_units,
                'status'       => $item->transaction_status,
                'agent'    => $item->aggregator_name,
                'date'         => \Carbon\Carbon::parse($item->timestamp)->toDateTimeString(),
                'BusinessHub'    => $item->business_hub ?? null,
                'ServiceUnit'    => $item->service_unit ?? null,
                'paytype'    => $item->pay_type ?? null,
                'payment_channel'    => $item->payment_channel ?? null,
                'custName'    =>  $item->cust_name ?? null,
                'dss'    => $item->dss_name ?? null,
                'AccountNo'    => $item->account_no ?? null,
                'feederName'    => $item->feeder_name ?? null,
                // 'Address'    =>  EcmiCustomers::where('MeterNo', $item->MeterNumber)->value('Address') ?? null,
                'serviceBand'   =>  $item->service_band ?? null,
                'token'   =>  $item->token_credit ?? null,
                'region'   =>  $item->region ?? null,
                'tariff'   =>  $item->tariff ?? null,
            ];
        });

        if(!$getTransaction){
           return $this->sendError('The Transaction does not exist', "Error!", Response::HTTP_BAD_REQUEST);
        }

        return $this->sendSuccess($getTransaction, 'Transaction history retrieved successfully', Response::HTTP_OK);    
              
    }

}
