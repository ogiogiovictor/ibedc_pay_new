<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Http;
use App\Models\EMS\ZoneCustomers;
use App\Models\EMS\BusinessUnit;
use App\Models\Transactions\PaymentTransactions;
use Carbon\Carbon;
use App\Jobs\PostPaidJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\EMS\ZoneBills;
use App\Models\CommissionSettings;
use App\Services\CommissionService;
use App\Services\PolarisLogService;



class PostPaidService extends BaseAPIController
{
   public function processService($checkRef, $request, $payment){

     $baseUrl = env('MIDDLEWARE_URL');
     $custInfo = ZoneCustomers::where("AccountNo", $request->account_id)->first();
     $buCode = BusinessUnit::where("BUID", $custInfo->BUID)->value("Name");

     //The log the payment response first
     (new PolarisLogService)->processLogs($checkRef->transaction_id, $request->account_id,  $custInfo->AccountNo, $payment);
     
     $addCustomerUrl = $baseUrl . 'vendelect';


     $data = [
        'meterno' => $request->account_id,
        'vendtype' => $checkRef->account_type,
        'amount' => $request->amount, //$payment['data']['amount'], 
        "custname" => $checkRef->customer_name,
        "businesshub" => isset($buCode) ? $buCode : $custInfo->BUID,
        "custphoneno" => $request->phone,
        "payreference" => $checkRef->transaction_id,
        "colagentid" => "IB001",
                         
    ];

    $response = Http::withoutVerifying()->withHeaders([
        'Authorization' => env('MIDDLEWARE_KEY'),
    ])->post($addCustomerUrl, $data);

    $newResponse =  $response->json();


    if($newResponse['status'] == "true"){ 
        //Update the status of payment and send the job and send SMS
        $update = PaymentTransactions::where("transaction_id", $checkRef->transaction_id)->update([
            'status' =>  'success', //"resp": "00",
            'receiptno' => isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] :  '',
            'Descript' =>  isset($newResponse['message']) ? $newResponse['message'] :  '',
            'units' => isset($newResponse['Units']) ? $newResponse['Units'] : '0', 
            'provider' => $request->provider,
            'minimumPurchase' => isset($newResponse['customer']['minimumPurchase']) ? $newResponse['customer']['minimumPurchase'] : '',
            'tariffcode'  => isset($newResponse['customer']['tariffcode']) ? $newResponse['customer']['tariffcode'] : '',
            'customerArrears' => isset($newResponse['customer']['customerArrears']) ? $newResponse['customer']['customerArrears'] : '',
          
            'udertaking' => isset($newResponse['customer']['businessUnitId']) ? $newResponse['customer']['businessUnitId'] : '',
        ]);


        if (!str_starts_with($checkRef->email, 'default')) {
            dispatch(new PostPaidJob($checkRef)); //$checkRef->account_type
        }
       


        //COMMISSION CALCULATION FOR AGENTS
        $user_authority = Auth::user();

        // if (in_array($custInfo->TariffID, [1, 4, 6, 7, 9, 11, 13, 16, 19]) &&  $user_authority->authority == 'agent' ) {
        //     // Your commission calculation logic here
        //     $this->calculateCommission($checkRef);
        // }
        
        
        \Log::info('Postpaid Payment Successful Response: ' . json_encode($newResponse));

        return $this->sendSuccess($checkRef, "Payment Successfully Completed", Response::HTTP_OK);

    } else {
        // $update = PaymentTransactions::where("transaction_id", $checkRef->transaction_id)->update([
        //     'status' =>  'processing', //"resp": "00",
        // ]);
        return $this->sendSuccess($checkRef, "Payment Successfully Completed", Response::HTTP_OK);
    }

   }



   private function calculateCommission($checkRef){


    //get the latest bill
    $latestBill = ZoneBill::where('AccountNo', $checkRef->account_number)
                               ->orderBy('created_at', 'desc')
                               ->first();

    //Get the Outstanding Balance,  last_payment
    $latestBillAmount = $latestBill->TotalDue;
    $getBalance = $this->outBalance($checkRef);

    //check the type of payment, if the payment is for the current charge.
    if($checkRef->payment_source == "current_charge"){

        //get the commission
        $commission = CommissionSettings::where("appied_to", 'current_charge')->first();

        if($latestBill->TotalDue <= 0) {
           // $commission = $checkRef->amount * $commission->percentage; // 0.5% commission
           // (new CommissionService)->processCommission($checkRef, $commission, $commission->percentage);
           (new CommissionService)->calculateCommission($latestBillAmount, $checkRef, $getBalance);
        }
            
    }

     // check if the customer have an outstanding balance;
    if($checkRef->payment_source == "oustanding_balance"){

        // Check if $getBalance is a number (positive or negative)
        if (!is_numeric($getBalance)) {
            //throw new \Exception("Invalid balance value. Balance must be a valid number.");
        }
        
        (new CommissionService)->calculateCommission($latestBillAmount, $checkRef, $getBalance);

    }
    


   }


   private function outBalance($request){

    $data = [
        "meter_number" => $request->account_number,
        "vendtype" => "Postpaid"
    ];

        try {

            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
            ])->post("https://middleware3.ibedc.com/api/v1/verifymeter", $data);
    
        
            $finalResponse = $response->json();
    
           
            return $finalResponse['data']['customerArrears'];
            

        }catch(\Exception $e) {

            return $e->getMessage();
        }

        
        

    
   }

//    private function isConsistentPayer($customerId, $months)
//     {
//         // Check if the customer has paid consistently for the past X months
//         $payments = Payment::where('customer_id', $customerId)
//                             ->where('created_at', '>=', now()->subMonths($months))
//                             ->count();

//         return $payments == $months;
//     }

    // private function calculateThreeMonthsPayment($customerId)
    // {
    //     // Calculate the total payments made by the customer in the past 3 months
    //     return Payment::where('customer_id', $customerId)
    //                 ->where('created_at', '>=', now()->subMonths(3))
    //                 ->sum('amount');
    // }



}
