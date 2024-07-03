<?php

namespace App\Repositories;

use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Transactions\PaymentTransactions;


class TransactionRepository implements TransactionRepositoryInterface
{
    public function index() {

    }

    public function store(array $transaction){

        return PaymentTransactions::create($transaction);
    }

    public function show($tid){

        return PaymentTransactions::where('transaction_id', $tid)->first();
    }

    public function mytransactions($user_id){
       // return $user_id;
        return PaymentTransactions::where('user_id', $user_id)->whereIn('status', ['processing', 'success', 'failed'])->orderby('created_at', 'desc')->paginate(10);
    }

    public function checkifexist($user_id, $account_no){
        return PaymentTransactions::where(['user_id' => $user_id, 'account_number' => $account_no])->first();
    }

    public function usernotification($user_id){
         // Fetch the latest successful payment transaction for the given user ID
        $transaction = PaymentTransactions::where('user_id', $user_id)
        ->whereIn('status', ['success'])
        ->orderBy('created_at', 'desc')
        ->first(); // Use 'first' instead of 'limit(1)' to get a single result

        return $transaction; // Return the fetched transaction
    }

}
