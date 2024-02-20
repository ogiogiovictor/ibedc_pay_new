<?php

namespace App\Interfaces;

interface TransactionRepositoryInterface
{
    
    public function index();
    public function store(array $request);
    public function show($transaction_id);
    public function mytransactions($user_id);
    public function  checkifexist($user_id, $account_no);
}
