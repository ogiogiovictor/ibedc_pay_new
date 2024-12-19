<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\TransactionRepositoryInterface;
use App\Interfaces\HomeRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\VendingRequest;
use App\Enums\TransactionEnum;
use App\Models\EMS\ZoneCustomers;
use App\Models\ECMI\EcmiCustomers;
use App\Models\ECMI\SubAccount;
use App\Models\EMS\BusinessUnit;
use App\Helpers\StringHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Services\AppService;

class PaymentController extends BaseAPIController
{
    private TransactionRepositoryInterface $transaction;
    private HomeRepositoryInterface $user;


    public function __construct(TransactionRepositoryInterface $transaction, HomeRepositoryInterface $user) {

        $this->transaction = $transaction;
        $this->user = $user;
    }

    public function index() {

    }

    public function store(PaymentRequest $request){
       // return $this->sendError("System Downtime", 'System Unavailable, please try again later', Response::HTTP_BAD_REQUEST); 


       if($request->amount < 1000) {
        return $this->sendError("Invalid Amount. Minimum Amount is 1,000", 'ERROR - minimum amount 1,000', Response::HTTP_BAD_REQUEST); 
       }
    
        try{

            if($request->account_type == TransactionEnum::Postpaid()->value){

                return $this->createPostPaidPayment($request);

            } else if($request->account_type == TransactionEnum::Prepaid()->value) {

                return $this->createPrePaidPayment($request);

            }else {
                return $this->sendError("Invalid Account Type", 'ERROR', Response::HTTP_BAD_REQUEST); 
            } 

        }catch(\Exception $e){
            return $this->sendError($e->getMessage(), 'ERROR', Response::HTTP_BAD_REQUEST); 
        }

    }

    public function show($tid){

    }


    private function createPostPaidPayment($request) {

        $checkService = (new AppService)->processApp("Postpaid");

        if ($checkService instanceof \Illuminate\Http\JsonResponse) {
            return $checkService; // Return the downtime error response
        }

        $checkRequest = $request->account_number;

        if(!$checkRequest){
            return $this->sendError('Error', "Invalid Key Sent", Response::HTTP_BAD_REQUEST);
        }

        $custInfo = ZoneCustomers::where("AccountNo", $request->account_number)->first();
        $auth = Auth::user();

        if(!$custInfo){
            return $this->sendError('Error', "No Record Found", Response::HTTP_NOT_FOUND);
        }

        if( strlen($custInfo->MeterNo) == '16') {
            return $this->sendError('Non STS Customer Cannot use this platform. Please visit our office"ror', "Error", Response::HTTP_BAD_REQUEST);
        }

        if (strpos($custInfo->AccountNo, '.') !== false) {
            return $this->sendError('Non STS Customer Cannot use this platform. Please visit our office', "Error", Response::HTTP_BAD_REQUEST);
        }

        $transactionID = StringHelper::generateUUIDReference();
        $checkTransID =  $this->transaction->show($transactionID);
        if($checkTransID) { $transactionID = StringHelper::generateUUIDReference(). ''.time().data('YmdHis'); }

        $buCode = BusinessUnit::where("BUID", $custInfo->BUID)->value("Name");

        DB::beginTransaction();


        try{
          
            $payment = $this->transaction->store([
                'email' => isset($request->email) ? $request->email : $this->user->index($auth->id)[0]->email,
                'transaction_id' => $transactionID,
                'phone' => isset($request->phone) ? $request->phone :  $this->user->index($auth->id)[0]->phone,
                'amount' => (float)$request->amount,
                'account_type' => $request->account_type,
                'account_number' => trim($request->account_number),
                'payment_source' => $request->payment_source,
                'status' => "started",
                'customer_name' => $custInfo->Surname.' '. $custInfo->FirstName,
                'date_entered' => Carbon::now(),
                'BUID' => isset($buCode) ? $buCode : $custInfo->BUID,
                'owner' => $request->owner,
                'latitude' => isset($request->latitude) ? $request->latitude : 'null',
                'longitude' => isset($request->longitude) ? $request->longitude : 'null',
                'source_type' => isset($request->source_type) ? $request->source_type : 'null',
                "user_id" => $auth->id,
                'agency' => $auth->agency,
                'Address' => $custInfo->Address1. ' '. $custInfo->Address2
            ]);

            if($payment){
                // Convert the $queryUser object to an array
                $queryUserArray = $payment->toArray();
                    
                // Add the URL to the array
                $queryUserArray["sub_account"] = $this->subaccountmatch($buCode);
                //$queryUserArray["user_object"] = $this->user->index($auth->id);

                DB::commit();
                return $this->sendSuccess($queryUserArray, "Payment Process Initiated", Response::HTTP_OK);
            }

        }catch(\Exception $e){
            DB::rollBack();
            //dispatch and email notifiying admin of the failed transaction;
            return $this->sendError('Error', "Error Initiating Payment: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    }





    ///////////////////////////////// CREATE PREPAID PAYMENT ////////////////////////////////
    public function createPrePaidPayment($request){

        $checkService = (new AppService)->processApp("Prepaid");

        if ($checkService instanceof \Illuminate\Http\JsonResponse) {
            return $checkService; // Return the downtime error response
        }
    
        $checkRequest = $request->MeterNo;

        if(!$checkRequest){
            return $this->sendError('Error', "Invalid Key Sent", Response::HTTP_BAD_REQUEST);
        }

        $zoneECMI = EcmiCustomers::where("MeterNo", $request->MeterNo)->first();

        if (!$zoneECMI) {
            return $this->sendError('ERROR', "Meter number does not exist", Response::HTTP_BAD_REQUEST);
        }


        if(strlen($request->MeterNo) == '16') {
            return $this->sendError('Error', "Non STS Customer Cannot use this platform. Please visit our office", Response::HTTP_BAD_REQUEST);
        }

        if (strpos($zoneECMI->AccountNo, '.') !== false) {
            return $this->sendError('Error', "Non STS Customer Cannot use this platform. Please visit our office", Response::HTTP_BAD_REQUEST);
        }

          // Check if transfer amount exceeds 25 million
        if($request->amount > 25000000) {
            return $this->sendError('Error', "Transaction Amount cannot be more than 25000000", Response::HTTP_BAD_REQUEST);
        }

        $data = [
            "meter_number" => $request->MeterNo,
            "vendtype" => "Prepaid"
        ];

        
        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
        ])->post("https://middleware3.ibedc.com/api/v1/verifymeter", $data);

        $newResponse =  $response->json();

       // \Log::info('VERIFICATION REPONSE: ' . json_encode($newResponse['data']));

        $minimumPurchase = isset($newResponse['data']['minimumPurchase']) ? $newResponse['data']['minimumPurchase'] : 0;

        if($request->amount < $minimumPurchase) {
            return $this->sendError('Error', "Transaction Amount cannot by less than ₦$minimumPurchase due to your pending outstanding", Response::HTTP_BAD_REQUEST);
        }


         // Check Customer Eligibility for Payment
        //  $eligibilityCheck = SubAccount::where('AccountNo', $zoneECMI->AccountNo)
        // ->whereIn('SubAccountAbbre', ['OUTBAL', 'OUTBAL2',  'LOSREV', 'PENCHG'])
        // ->whereIn('ModeOfPayment', ['MONTHLY PAYMENT', 'One-off'])
        // ->first();

    
        // If Customer have outstanding return the error message
        // if($eligibilityCheck){
        //     $balance = floatval($eligibilityCheck->Balance);
        //         if($request->amount < $eligibilityCheck->PaymentAmount && $balance != 0.00){
        //             $formatCurrency = number_format($eligibilityCheck->PaymentAmount);

        //             return $this->sendError('Error', "Transaction Amount cannot by less than ₦$formatCurrency due to your pending outstanding", Response::HTTP_BAD_REQUEST);
        //         }
        // }


        $transactionID = StringHelper::generateUUIDReference();
        DB::beginTransaction();
        $auth = Auth::user();

        try{

            $payment = $this->transaction->store([
                'email' => isset($request->email) ? str_replace(' ', '', $request->email) : $this->user->index($auth->id)[0]->email,
                'transaction_id' => $transactionID,
                'phone' => isset($request->phone) ? $request->phone : $this->user->index($auth->id)[0]->phone,
                'amount' => (float)$request->amount,
                'account_type' => $request->account_type,
                'account_number' => trim($zoneECMI->AccountNo),
                'payment_source' => $request->payment_source,
                'status' => "started",
                'meter_no' => $request->MeterNo,
                'customer_name' => $zoneECMI->Surname.' '. $zoneECMI->OtherNames,
                'date_entered' => Carbon::now(),
                'BUID' => $zoneECMI->BUID,
                'owner' => $request->owner,
                'latitude' => isset($request->latitude) ? $request->latitude : 'null',
                'longitude' => isset($request->longitude) ? $request->longitude : 'null',
                'source_type' => isset($request->source_type) ? $request->source_type : 'null',
                "user_id" => $auth->id,
                'agency' => $auth->agency,
                'Address' => $zoneECMI->Address
            ]);

            if($payment){
                // Convert the $queryUser object to an array
                $queryUserArray = $payment->toArray();
                    
                // Add the URL to the array
                $queryUserArray["sub_account"] = $this->subaccountmatch($zoneECMI->BUID);
               // $queryUserArray["user_object"] =  $this->user->index($auth->id);

                DB::commit();
                return $this->sendSuccess($queryUserArray, "Payment Process Initiated", Response::HTTP_OK);
            }



        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Error', "Error Initiating Payment: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    }




    public function continuePayment(Request $request){

        $checkTrans = $this->transaction->show($request->tx_ref);

        //Check for undefined and Null paymentRef
        if(!$request->redirect_url){
            return $this->sendError('Invalid Redirect!', 'Error!', Response::HTTP_UNAUTHORIZED);
        }

        if(!$checkTrans){
            return $this->sendError('The Transaction does not exist', "Error!", Response::HTTP_BAD_REQUEST);
        }

        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer FLWSECK-effa327dab3411ddeb7730dd0e5e38bf-191d6565d8dvt-X',  // Live Keys
           //'Authorization' => 'Bearer FLWSECK_TEST-3d68b68ad292988f76a5ea280acf6521-X', //Test Key
            'Content-Type' => "application/json"
        ])->post("https://api.flutterwave.com/v3/payments", $request->all());

        $newResponse =  $response->json();

        return $this->sendSuccess($newResponse, "Proceed to Make Payment", Response::HTTP_OK);

    }


    public function retryPayment(Request $request) {
        
    }











    ////////////////////////// CUSTOMER SUB ACCOUNT CREATION FLUTTERWAVE POLARIS//////////////////////////
    private function subaccountmatch($bhub){

        $result = match($bhub) {
            'Apata' => 'RS_918A1A52B723F63BB575BADD5481EC5A',
            'APATA' => 'RS_918A1A52B723F63BB575BADD5481EC5A',
            
            'Baboko' => 'RS_21483F82BEAC6179FFBE636F803DB0AF',
            'BABOKO' => 'RS_21483F82BEAC6179FFBE636F803DB0AF',

            'Challenge' => 'RS_BC10DF9A764E86126B0FCA771989BC17',
            'CHALLENGE' => 'RS_BC10DF9A764E86126B0FCA771989BC17',

            'Dugbe' => 'RS_6C233B30D09D7BEA51F98BD309B85D2B',
            'DUGBE' => 'RS_6C233B30D09D7BEA51F98BD309B85D2B',

            'Ede' => 'RS_8804F6AAAE8BC8AE2CCF2AF4666FAB96',
            'EDE' => 'RS_8804F6AAAE8BC8AE2CCF2AF4666FAB96',

            'Ijebu-Igbo' => 'RS_0D26FB11831AA4D1D7833E1AC4F693BE',
            'Ijebu-Ode' => 'RS_0D26FB11831AA4D1D7833E1AC4F693BE',
            'IJEBU' =>  'RS_0D26FB11831AA4D1D7833E1AC4F693BE',

            'Ijeun' => 'RS_5156C00508CD613ED215325BA1911C40',
            'IJEUN'  => 'RS_5156C00508CD613ED215325BA1911C40',

            'Ikirun' => 'RS_5BF7C5A21408A6FA5B128D4B0E50A911',
            'IKIRUN' => 'RS_5BF7C5A21408A6FA5B128D4B0E50A911',

            'Ile-Ife' => 'RS_685D4F4D8E4D8A742625C0EE1E46A0BB',
            'ILEIFE' => 'RS_685D4F4D8E4D8A742625C0EE1E46A0BB',

            'Ilesha' => 'RS_556B4742EB3BF47BD2A533D22AC8996B',
            'ILESHA' =>  'RS_556B4742EB3BF47BD2A533D22AC8996B',
            'Ilesa' => 'RS_556B4742EB3BF47BD2A533D22AC8996B',

            'Jebba' => 'RS_0CB38D8A61E117D0A6CD5FC3D5A44B82',
            'JEBBA' => 'RS_0CB38D8A61E117D0A6CD5FC3D5A44B82',
            'New Bussa' => 'RS_0CB38D8A61E117D0A6CD5FC3D5A44B82',

            'Molete' => 'RS_DDA531C2E10E279D2CDA9229DA18B2C5',
            'MOLETE' => 'RS_DDA531C2E10E279D2CDA9229DA18B2C5',

            'Mowe-Ibafo' => 'RS_AD5E81FEB06D7E767D81BF2465CE3A7C',
            'MOWE IBAFO' => 'RS_AD5E81FEB06D7E767D81BF2465CE3A7C',
            'MOWEIBAFO' => 'RS_AD5E81FEB06D7E767D81BF2465CE3A7C',
            'MOWE-IBAFO' => 'RS_AD5E81FEB06D7E767D81BF2465CE3A7C',

            'Ogbomosho' => 'RS_C878B0811A8B994E049904C96A8C8D9C',
            'OGBOMOSO' => 'RS_C878B0811A8B994E049904C96A8C8D9C',

            'Ojoo' => 'RS_4C39DAB18D50D1ECDEC3AB8E8B2DB92D',
            'OJOO' => 'RS_4C39DAB18D50D1ECDEC3AB8E8B2DB92D',

            'Olumo' => 'RS_B54207572B744CA40A67A2AC9F7DE4C9',
            'OLUMO' => 'RS_B54207572B744CA40A67A2AC9F7DE4C9',

            'Omu-Aran' => 'RS_8D1CFE6891D82875B4600A011F97F4AD',
            'OmuAran' => 'RS_8D1CFE6891D82875B4600A011F97F4AD',
            'OMUARAN' => 'RS_8D1CFE6891D82875B4600A011F97F4AD',

            'Osogbo' => 'RS_864CA47D88E14691083FDD56C8EC0654',
            'OSOGBO' => 'RS_864CA47D88E14691083FDD56C8EC0654',

            'Ota' => 'RS_73617BBB5DEC830B27C504A07D3D22EC',
            'OTA' => 'RS_73617BBB5DEC830B27C504A07D3D22EC',

            'Oyo' => 'RS_F7087CC1DE6AF43BBF63E5DAC674A3A9',
            'OYO' => 'RS_F7087CC1DE6AF43BBF63E5DAC674A3A9',

            'Sagamu' => 'RS_2E115B9B3D616A93B6A3BB82E86A314E',
            'SAGAMU' => 'RS_2E115B9B3D616A93B6A3BB82E86A314E',
            'SHAGAMU' => 'RS_2E115B9B3D616A93B6A3BB82E86A314E',

            'Sango' => 'RS_A3EDD2AF05B108DCC4996F2DF09DE5B8',
            'SANGO' => 'RS_A3EDD2AF05B108DCC4996F2DF09DE5B8',
            'Akanran' => 'RS_A3EDD2AF05B108DCC4996F2DF09DE5B8',

            'MONATAN' => 'RS_0CEC5358F6973CD08C4CD862640E4625',
            default => 'RS_918A1A52B723F63BB575BADD5481EC5A',  // offa is default
        };

        return $result;

    }

}
