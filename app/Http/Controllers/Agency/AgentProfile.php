<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agency\Agents;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Models\User;
use App\Models\Transactions\PaymentTransactions;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AgentProfile extends BaseAPIController
{
    public function getProfile() {

        $user = auth()->user();

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $monthlyCollection = PaymentTransactions::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)->where('user_id', $user->id)->select(DB::raw("SUM(CAST(amount AS float)) as total"))->value('total');

        $yearlyCollection = PaymentTransactions::whereYear('created_at', $currentYear)->where('user_id', $user->id)->select(DB::raw("SUM(CAST(amount AS float)) as total"))->value('total');


         $lastTenCollections = PaymentTransactions::where('user_id', $user->id) // optional filter
        ->latest() // same as orderBy('created_at', 'desc')
        ->take(10)
        ->get();

        $data = [
            'user' => $user,
            'wallet' => $user->wallet,
            'account' => $user->virtualAccount,
            'monthly_collection' => $monthlyCollection,
            'total_yearly_collection' => $yearlyCollection,
            'commission' => 0,
        ];

         return $this->sendSuccess($data, 'PROFILE LOADED', Response::HTTP_OK);
       

    }


    public function getHistory() {


         $user = auth()->user();

         $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

         $lastTenCollections = PaymentTransactions::where('user_id', $user->id) // optional filter
        ->latest() // same as orderBy('created_at', 'desc')
        ->take(10)
        ->get();

        $data = [
            'user' => $user,
            'recent_collections' => $lastTenCollections,
            'commission' => 0,
        ];

         return $this->sendSuccess($data, 'PROFILE LOADED', Response::HTTP_OK);

    }


    public function getpaymentbyBH(Request $request){

        $businesshub = $request->query('businesshub'); // optional query filter

        if(!$businesshub) {
            return $this->sendError('Please provide business hub', "Error!", Response::HTTP_BAD_REQUEST);
        }

        $businesshubpayment = PaymentTransactions::where('BUID', $businesshub) 
        ->paginate(10);

        $data = [
            'payment_by_bh' => $businesshubpayment
        ];

         return $this->sendSuccess($data, 'PAYMENT LOADED', Response::HTTP_OK);

    }
}
