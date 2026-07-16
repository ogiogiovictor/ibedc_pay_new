<?php

namespace App\Models\NAC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UBVSFeeders extends Model
{
    use HasFactory;

    protected $table = "MAIN_WAREHOUSE.dbo.ubvs_feeders";

    protected $connection = 'data_warehouse';
    
}
