<?php

namespace App\Models\NAC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regions extends Model
{
    use HasFactory;

     protected $table = "MAIN_WAREHOUSE.dbo.RH_BH_Data_for_OPS";

    protected $connection = 'data_warehouse';

    public $timestamps = false;
}
