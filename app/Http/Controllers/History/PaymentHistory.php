<?php

namespace App\Http\Controllers\History;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\TransactionRepositoryInterface;
use App\Http\Resources\PaymentResource;
use App\Models\ECMI\EcmiPayments;
use App\Models\EMS\ZonePayments;
use App\Models\EMS\ZoneBills;
use App\Http\Resources\PrepaidResource;

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

       $queryHistory =  $this->transaction->mytransactions(Auth::user());
        //$queryHistory =  $this->transaction->mytransactions(Auth::user()->id);
        return $this->sendSuccess($queryHistory, "History Successfully Loaded", Response::HTTP_OK);
    }

    public function getOtherHistory() {

        $user = Auth::user();

        if($user->account_type == 'Prepaid' || $user->account_type == 'prepaid') {
            return PrepaidResource::collection(EcmiPayments::where("MeterNo" , $user->meter_no_primary)->orderby("TransactionDateTime", "desc")->paginate(10));
        } else if($user->account_type == 'Postpaid') {
           return ZonePayments::where('AccountNo', $user->meter_no_primary)->orderby("PayDate", "desc")->paginate(10);
        } else {
            return "Please update your profile and map the correct Meter/Account No ". $user->meter_no_primary;
        }
    }


    public function getBillHistory() {

        $user = Auth::user();

        $ZoneBills = ZoneBills::where('AccountNo', $user->meter_no_primary)->orderby("Billdate", "desc")->paginate(10);

        if(empty($ZoneBills)) {
            return "Please update your profile and map the correct Account No ". $user->meter_no_primary;
        } else {
             return  $ZoneBills;   
        }
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
