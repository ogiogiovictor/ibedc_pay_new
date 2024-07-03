<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ECMI\EcmiCustomers;
use App\Models\EMS\ZoneCustomers;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_id' => $this->transaction_id,
            'user_id' => $this->user_id,
            'phone' => $this->phone,
            'amount' => $this->amount,
            'account_type' => $this->account_type,
            'account_number' => $this->account_number,
            'meter_no' => $this->meter_no,
            'status' => $this->status,
            'customer_name' => $this->customer_name,
            'payment_source' => $this->payment_source,
            'provider' => $this->provider,
            'providerRef' => $this->providerRef,
            'date_entered' => $this->date_entered,
            'receiptno' => $this->receiptno,
            'owner' => $this->owner,
            'BUID' => $this->BUID,
            'Descript' => $this->Descript,
            'response_status' => $this->response_status,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'source_type' => $this->source_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'units' => $this->units,
            'costOfUnits' => $this->costOfUnits,
            'VAT' => $this->VAT,
            'agency' => $this->agency,
            'minimumPurchase' => $this->minimumPurchase,
            'tariffcode' => $this->tariffcode,
            'customerArrears' => $this->customerArrears,
            'tariff' => $this->tariff,
            'serviceBand' => $this->serviceBand,
            'feederName' => $this->feederName,
            'dssName' => $this->dssName,
            'udertaking' => $this->udertaking,
            'Address' => $this->getAddress($this->account_type),
        ];
        //return parent::toArray($request);
    }


    public function getAddress($accountType) {
        if($accountType == "Prepaid"){
            return EcmiCustomers::where("MeterNo",  $this->meter_no)->value("Address");
        }else {
           // $checkAddress = ZoneCustomers::where("AccountNo",  $this->account_number)->first();
            //return  $checkAddress->Address1. " ". $checkAddress->Address2. " ". $checkAddress->City. " ". $checkAddress->State;
            return ZoneCustomers::where("AccountNo",  $this->account_number)->value("Address1");   
        }
    }


   
}

              
