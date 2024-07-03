<?php

namespace App\Http\Controllers\History;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\TransactionRepositoryInterface;
use App\Http\Resources\PaymentResource;

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
       // return Auth::user()->id;

        $queryHistory =  $this->transaction->mytransactions(Auth::user()->id);
        return $this->sendSuccess($queryHistory, "History Successfully Loaded", Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource. PrepaidResource::collection($getTransaction)  PaymentResource::collection($queryHistory)
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
