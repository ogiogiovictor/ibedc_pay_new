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
        return PaymentTransactions::where('user_id', $user_id)->orderby('created_at', 'desc')->paginate(10);
    }

    public function checkifexist($user_id, $account_no){
        return PaymentTransactions::where(['user_id' => $user_id, 'account_no' => $account_no])->first();
    }
}
