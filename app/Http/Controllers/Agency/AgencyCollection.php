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
                'monthly_target' => '',
                'agency_monthly_target' =>  number_format($agencyMonthlyTarget->target_amount, 2),
            ];


            return $this->sendSuccess($agencyAggreation, "Agent Collection", Response::HTTP_OK);

        }catch(\Exception $e){

            return $this->sendError($e->getMessage(), 'ERROR', Response::HTTP_UNAUTHORIZED);

        }

       
        

    }
}
