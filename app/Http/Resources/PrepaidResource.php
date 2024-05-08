<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrepaidResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'TransactionDateTime' => $this->TransactionDateTime,
            'BUID' => $this->BUID,
            'transref' => $this->transref,
            'Token' => $this->Token,
            'AccountNo' => $this->AccountNo,
            'Surname' => $this->Surname,
            'OtherNames' => $this->OtherNames,
            'MeterNo' => $this->MeterNo,
            'Amount' => $this->Amount,
            'Units' => $this->Units,
            'CostOfUnits' => $this->CostOfUnits,
            'VAT' => $this->VAT,
            
        ];
       // return parent::toArray($request);
    }
}
