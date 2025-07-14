<?php

namespace App\Services;

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Models\CommissionHistory;
use App\Models\Transactions\PaymentTransactions;
use Illuminate\Support\Facades\Auth;
use App\Models\CommissionSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use App\Models\EMS\ZoneBills;
use Illuminate\Support\Facades\Http;
use App\Models\EMS\ZonePayments;
use App\Models\CommissionLog;

class CommissionService  extends BaseAPIController
{
    public function calculateCommission($latestBillAmount, $checkRef, $getBalance) {
        $user = Auth::user();

        if(is_numeric($getBalance)){
            $createCommission = CommissionHistory::create([
                'user_id' => $user->id,
                'amount_paid' => $checkRef->amount,
                'outstanding' => $getBalance,
                'commission_amount' =>  ($getBalance - $checkRef->amount),
                'commission_percent' => $commissionPercent,
                'acount_type' => 'Postpaid',
                'account_id' => $checkRef->account_number,
                'agency' => $checkRef->agency,
                'transaction_id' => $checkRef->id,
                'status_settled'=> 'Pending',
                'bill_year' => date('Y'),
                'bill_month' => date('m'),
                'bill_amount' => $latestBillAmount
            ]);
        }
       
    }

    //'bill_year', 'bill_month', 'bill_amount'
    public function processCommission($checkRef, $commission, $commissionPercent)
    {

        $checkIfExist = CommissionHistory::where("transaction_id", $checkRef->id)->first();
        $user = Auth::user();

        //First check if the payment is successful and the commission for that payment is already paid
        if($checkRef->status == "success"){
            //Then check if the commission has already been paid
            if(!$checkIfExist && $user->id == $checkRef->user_id){
                //Then create the commission
                $createCommission = CommissionHistory::create([
                    'user_id' => $user->id,
                    'amount_paid' => $checkRef->amount,
                    'commission_amount' =>  Crypt::encrypt($commission),
                    'commission_percent' => $commissionPercent,
                    'acount_type' => 'Postpaid',
                    'account_id' => $checkRef->account_number,
                    'agency' => $checkRef->agency,
                    'transaction_id' => $checkRef->id
                ]);

                if($user->wallet) {
                    $currentCommission = $user->wallet->commission_amount;
                    // Check if the current commission is NULL or 0
                    if (is_null($currentCommission) || $currentCommission == 0) {
                        $decryptedCurrentCommission = 0;
                    } else {
                        // Decrypt the current commission amount
                        $decryptedCurrentCommission = Crypt::decrypt($currentCommission);
                    }

                    // Add the new commission
                    $newCommissionAmount = $decryptedCurrentCommission + abs($commission);

                    // Encrypt the new commission amount
                    $encryptedNewCommissionAmount = Crypt::encrypt($newCommissionAmount);

                    // Save the new encrypted commission amount back to the wallet
                    $user->wallet->update(['commission_amount' => $encryptedNewCommissionAmount]);

                   // $addtoWallet = $user->wallet->increment('commission_amount', $commission);
                } else {
                    \Log::warning('User wallet not found for user ID: ' . $user->id);
                }

              //  if($user->wallet){
                  //  $addtoWallet =  $user->wallet ? $user->wallet->increment('commission_amount', $commission) : 0;
              //  }
               

            }

        } else {

            \Log::info('No Commission Response: ' . json_encode($checkIfExist));

        }
          
    }



    
    public function commissioncalculation($checkRef) {

        $tariffCode = $this->customerType($checkRef);
        $monthsToCheck = strtoupper($tariffCode) === 'MD1' ? 6 : 3;

        $latestBill = ZoneBill::where('AccountNo', $checkRef->account_number)
                            ->orderByDesc('BillYear')
                            ->orderByDesc('BillMonth')
                            ->first();

        if (!$latestBill) {
             \Log::info('No bill found ' . json_encode($checkRef));
          //  return response()->json(['error' => 'No bill found'], 404);
        }

        // Fetch last N payments NOT YET USED
        $pastPayments = ZonePayments::where('AccountNo', $checkRef->account_number)
            ->orderByDesc('PayYear')
            ->orderByDesc('PayMonth')
            ->get()
            ->filter(function ($payment) use ($checkRef) {
                return !CommissionLog::where('account_number', $checkRef->account_number)
                                    ->where('pay_month', $payment->PayMonth)
                                    ->where('pay_year', $payment->PayYear)
                                    ->exists();
            })
            ->take($monthsToCheck);

        if ($pastPayments->isEmpty()) {
             \Log::info('No new payments found for commission ' . json_encode($checkRef));
           // return response()->json(['error' => 'No new payments found for commission'], 404);
        }

        $totalPaid = $pastPayments->sum('Payments');
        $balance = $latestBill->TotalDue - $totalPaid;
        $commission = $balance > 0 ? round($balance * 0.05, 2) : 0;

        // Log used payments
        foreach ($pastPayments as $payment) {
            CommissionLog::create([
                'account_number' => $checkRef->account_number,
                'pay_month' => $payment->PayMonth,
                'pay_year' => $payment->PayYear,
                'total_amount' => $payment->totalPaid,
                'total_due' =>  $latestBill->TotalDue,
                'amount' =>  $balance,
                'commission' => $commission,
                'payment_id' => $payment->PaymentID, // optional
                'agency' => Auth::user()->agency,
                'user_id' => Auth::user()->id,
                'agency' => Auth::user()->agency,
            ]);
        }

         \Log::info('Comission Successfuly Created' . json_encode($checkRef));

    // return response()->json([
    //     'account_number' => $checkRef->account_number,
    //     'customer_type' => $tariffCode,
    //     'months_considered' => $pastPayments->count(),
    //     'total_paid' => $totalPaid,
    //     'current_due' => $latestBill->TotalDue,
    //     'balance' => $balance,
    //     'commission' => $commission,
    // ]);

        
    }


    private function customerType($request){

            $data = [
                "meter_number" => $request->account_number,
                "vendtype" => "Postpaid"
            ];

                try {

                    $response = Http::withoutVerifying()->withHeaders([
                        'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
                    ])->post("https://middleware3.ibedc.com/api/v1/verifymeter", $data);
            
                
                    $finalResponse = $response->json();
            
                
                    return $finalResponse['data']['tariffcode'];
                    

                }catch(\Exception $e) {

                    return $e->getMessage();
                }
        
    }

}
