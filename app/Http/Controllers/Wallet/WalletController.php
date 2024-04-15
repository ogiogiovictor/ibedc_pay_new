<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet\WalletUser;
use App\Models\Wallet\WalletHistory;

class WalletController extends BaseAPIController
{
    public function retrieve(){

         $auth = Auth::user();
        $balance = WalletUser::where("user_id", $auth->id)->first();

        return $this->sendSuccess([
            'user_balance' => $balance,
            'balance_history' => WalletHistory::where('user_id', $auth->id)->get(),
         ], 'PROFILE LOADED', Response::HTTP_OK);
    }


    public function walletSummary(){


        // Example: Get monthly summary for March 2024
        $year = date('Y');
        $month = date('m');

        $monthlySummary = WalletHistory::monthlySummary($year, $month);

        return $this->sendSuccess([
            'wallet' => $monthlySummary,
         ], 'WALLET INFORMATION', Response::HTTP_OK);
        
    }
}
