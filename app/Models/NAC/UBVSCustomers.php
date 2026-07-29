<?php

namespace App\Models\NAC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UBVSCustomers extends Model
{
    use HasFactory;

    protected $table = "MAIN_WAREHOUSE.dbo.ubvs_customers";

    protected $connection = 'data_warehouse';

    protected $guarded = [];
}
