<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostpaidResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'PaymentID' => $this->PaymentID,
            'BillID' => $this->BillID,
            'receiptnumber' => $this->receiptnumber,
            'MeterNo' => $this->MeterNo,
            'AccountNo' => $this->AccountNo,
            'PayDate' => $this->PayDate,
            'Payments' => $this->Payments,
        ];
        //return parent::toArray($request);
    }
}
