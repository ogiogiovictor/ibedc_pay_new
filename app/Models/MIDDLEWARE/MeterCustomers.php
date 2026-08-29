<?php

namespace App\Models\MIDDLEWARE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterCustomers extends Model
{
    use HasFactory;

     protected $table = "MSMS_NEW.restored.customers";

    protected $connection = 'msms';

    public $timestamps = false;

    public function allocations()
    {
        return $this->hasMany(MeterAllocation::class, 'customerid', 'id');
    }

    public function payments()
    {
        return $this->hasMany(PaymentRecords::class, 'customer_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Stores::class, 'hub', 'id');
    }
}
