<?php

namespace App\Models\NAC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DSS extends Model
{
    use HasFactory;

    protected $table = "MAIN_WAREHOUSE.dbo.gis_dss";

    protected $connection = 'data_warehouse';

    public $timestamps = false;
    protected $primaryKey = 'Assetid'; // replace with actual primary key
    public $incrementing = false; // if not integer
    protected $keyType = 'string'; // or 'int'
}
