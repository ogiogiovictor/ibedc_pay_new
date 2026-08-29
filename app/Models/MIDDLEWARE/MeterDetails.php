<?php

namespace App\Models\MIDDLEWARE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterDetails extends Model
{
    use HasFactory;

     protected $table = "MSMS_NEW.restored.meterdetails_tbl";

    protected $connection = 'msms';

    public $timestamps = false;

    public function allocations()
    {
        return $this->hasMany(MeterAllocation::class, 'meterid', 'id');
    }
}
