<?php

namespace App\Http\Controllers\History;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\TransactionRepositoryInterface;

class PaymentHistory extends BaseAPIController
{
    private TransactionRepositoryInterface $transaction;


    public function __construct(TransactionRepositoryInterface $transaction) {

        $this->transaction = $transaction;
    }

    public function index() {

    }


    /**
     * Display the specified resource.
     */
    public function getHistory()
    {
        $queryHistory =  $this->transaction->mytransactions(Auth::user()->id);
        return $this->sendSuccess($queryHistory, "History Successfully Loaded", Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

}
