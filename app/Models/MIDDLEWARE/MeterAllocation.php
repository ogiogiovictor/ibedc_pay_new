<?php

namespace App\Models\MIDDLEWARE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterAllocation extends Model
{
    use HasFactory;

    protected $table = "MSMS_NEW.restored.map_meter_allocation_tbl";

    protected $connection = 'msms';

    public $timestamps = false;

    public function customer()
    {
        return $this->belongsTo(MeterCustomers::class, 'customerid', 'id');
    }

    public function meter()
    {
        return $this->belongsTo(MeterDetails::class, 'meterid', 'id');
    }
}
