<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ECMI\EcmiCustomers;

class ECMIPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
           // 'id' => $this->id,
            'transaction_id' => $this->TransactionNo,
            'user_id' => $this->OperatorID,
           // 'phone' => $this->phone,
            'amount' => $this->Amount,
            'account_type' => 'Prepaid',
            'account_number' => $this->AccountNo,
            'meter_no' => $this->MeterNo,
           // 'status' => $this->status,
            'customer_name' => EcmiCustomers::where("MeterNo", $this->MeterNo)->value('Surname'). " ". EcmiCustomers::where("MeterNo", $this->MeterNo)->value('OtherNames'),
           // 'payment_source' => $this->payment_source,
            'provider' => '',
            'providerRef' => $this->transref,
            'date_entered' => $this->TransactionDateTime,
            'receiptno' => $this->Token,
            'owner' => '',
            'BUID' => $this->BUID,
            'Descript' => $this->Reasons,
            'response_status' => $this->TransactionComplete,
            'latitude' => '',
            'longitude' => '',
            'source_type' => $this->OperatorID,
            'created_at' => $this->TransactionDateTime,
            'updated_at' => $this->TransactionDateTime,
            'deleted_at' => '',
            'units' => $this->Units,
            'costOfUnits' => $this->CostOfUnits,
            'VAT' => $this->VAT,
            'agency' => '',
            'minimumPurchase' => '',
            'tariffcode' => '',
            'customerArrears' => '',
            'tariff' => '',
            'serviceBand' => '',
            'feederName' => '',
            'dssName' => '',
            'udertaking' => '',
            'Address' => EcmiCustomers::where("MeterNo", $this->MeterNo)->value('Address'),
        ];
        //return parent::toArray($request);
    }
}
