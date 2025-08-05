<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agency\Agents;
use App\Models\Agency\Targets;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Transactions\PaymentTransactions;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\CommissionLog;
use App\Models\EMS\ZoneBills;
use Illuminate\Support\Facades\Http;
use App\Models\EMS\ZonePayments;
use App\Models\EMS\ZoneCustomers;

class AgencyCollection extends BaseAPIController
{
    public function agentCollection() {


        try {

            $auth = Auth::user();

            //Agency Collection Monthly
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        
            $monthly = PaymentTransactions::where("user_id", $auth->id)->whereIn("status", ["processing", "success"])->whereBetween("created_at", [$startDate, $endDate])
            ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(18, 2))) AS DECIMAL(18, 2)) AS sum_amount'))
                    ->first()
                    ->sum_amount;
    
            
            $monthly = (float)$monthly;
    
    
            //Agency Collection Today
            $today = now()->toDateString();
            $today_transactions = PaymentTransactions::where("user_id", $auth->id)->whereIn("status", ["processing", "success"])->whereDate("created_at", $today)
            ->select(DB::raw('CAST(SUM(CAST(amount AS DECIMAL(18, 2))) AS DECIMAL(18, 2)) AS sum_amount'))
                    ->first()
                    ->sum_amount;
    
            $today_transactions = (float)$today_transactions;

            $currentYear = Carbon::now()->year;
            $currentMonth = Carbon::now()->month;

            $agencyMonthlyTarget = Targets::where(["agency_id" => $auth->agency, "year" => $currentYear,  "month" => $currentMonth ])->first();
    
            $agencyAggreation = [
                'collection_for_the_month' => $monthly,
                'collection_for_today' => $today_transactions,
               // 'monthly_target' => 0,
                'agency_monthly_target' =>  isset($agencyMonthlyTarget->target_amount) ? number_format($agencyMonthlyTarget->target_amount, 2) : 0,
            ];


            return $this->sendSuccess($agencyAggreation, "Agent Collection", Response::HTTP_OK);

        }catch(\Exception $e){

            return $this->sendError($e->getMessage(), 'ERROR', Response::HTTP_UNAUTHORIZED);

        }

    }


    public function agencyCollection() {

    }


    public function commission() {


        $getcommission = CommissionLog::all();
        return $this->sendSuccess($getcommission, "All Commission", Response::HTTP_OK);

    }


    public function commissioncalculation(Request $request){

        $tariffCode = $this->customerType($request);
        $monthsToCheck = strtoupper($tariffCode) === 'MD1' ? 6 : 3;

         // Get the last 3 bills
        $recentBills = ZoneBills::where('AccountNo', $request->account_number)
            ->orderByDesc('BillYear')
            ->orderByDesc('BillMonth')
            ->take($monthsToCheck)
            ->get();

      
        if ($recentBills->isEmpty()) {
            \Log::info('No bill found ' . json_encode($checkRef));
            return;
        }

         $totalBillAmount = $recentBills->sum('TotalDue');

         // Get last 3 months payments that haven’t been used for commission
        $recentPayments = ZonePayments::where('AccountNo', $request->account_number)
            ->orderByDesc('PayYear')
            ->orderByDesc('PayMonth')
            ->get()
            ->filter(function ($payment) use ($request) {
                return !CommissionLog::where('account_number', $request->account_number)
                    ->where('pay_month', $payment->PayMonth)
                    ->where('pay_year', $payment->PayYear)
                    ->exists();
            })
            ->take($monthsToCheck);

       

        if ($recentPayments->isEmpty()) {
            \Log::info('No new payments found for commission ' . json_encode($request));
            return;
        }

        $totalPaid = $recentPayments->sum('Payments');
        $balance = $totalPaid - $totalBillAmount + $request->amount;

       

        // Only calculate commission if balance is positive
        $commission = $balance > 0 ? round($balance * 0.05, 2) : 0;

        foreach ($recentPayments as $payment) {
            CommissionLog::create([
                'account_number' => $request->account_number,
                'pay_month' => $payment->PayMonth,
                'pay_year' => $payment->PayYear,
                'total_amount' => $totalPaid,
                'total_due' => $totalBillAmount,
                'amount' => $balance,
                'commission' => $commission,
                'agency' => Auth::user()->agency,
                'user_id' => Auth::user()->id,
                'payment_id' => $payment->PaymentID, // <- Make sure this is present
            ]);
        }

        \Log::info('Commission Successfully Created for: ' . $request->account_number);

          return response()->json([
            'account_number' => $request->account_number,
            'customer_type' => $tariffCode,
            'total_due' => $totalBillAmount,
            'total_paid' => $totalPaid,
            'balance' => $balance,
            'commission' => $commission,
        ]);


    }


     private function customerType($request){

            $data = [
                "meter_number" => $request->account_number,
                "vendtype" => "Postpaid"
            ];

                try {

                    $response = Http::withoutVerifying()->withHeaders([
                        'Authorization' => 'Bearer LIVEKEY_5AEB0A6DDCAF91D938E5C56644313129',
                    ])->post("https://middlewarelookup.ibedc.com/lookup/verify-meter/", $data);   //https://middleware3.ibedc.com/api/v1/verifymeter  |  LIVEKEY_711E5A0C138903BBCE202DF5671D3C18
            
                
                    $finalResponse = $response->json();
            
                
                    return $finalResponse['data']['tariffcode'];
                    

                }catch(\Exception $e) {

                    return $e->getMessage();
                }
        
    }


    
}
