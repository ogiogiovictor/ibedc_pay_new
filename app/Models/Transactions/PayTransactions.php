<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayTransactions extends Model
{
    use HasFactory;

    protected $table = "IBEDC_AMI_ENGINE.dbo.payment_logs";

    protected $connection = 'ibedc_engine';


    public function ibedcpayTransactions(){
        return $this->where('status', 'pending')
                    ->whereNotNull('providerRef')
                    ->whereNull('receiptno')
                    ->orderby("created_at", "desc")
                    ->get();
    }


    public function agencyTransaction($agency){
        return $this->where('status', 'pending')
                    ->whereNotNull('providerRef')
                    ->whereNull('receiptno')
                    ->where('agency', $agency)
                    ->orderby("created_at", "desc")
                    ->get();
    }

   
}
