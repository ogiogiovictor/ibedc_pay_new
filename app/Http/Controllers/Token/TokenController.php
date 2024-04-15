<?php

namespace App\Http\Controllers\Token;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Auth;
use App\Models\Transactions\PaymentTransactions;


class TokenController extends BaseAPIController
{
    public function GetNotification() {

        $user = Auth::user();

            // Retrieve payment transactions for the authenticated user
         $paymentTransactions = PaymentTransactions::where("user_id", $user->id)->latest()->limit(2)->get();

        // Convert payment transactions to array
        $transactionsArray = $paymentTransactions->toArray();

        return $this->sendSuccess([
            'token' => $transactionsArray,
         ], 'TOKEN LOADED', Response::HTTP_OK);
    }
}
