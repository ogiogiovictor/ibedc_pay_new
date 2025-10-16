<?php

namespace App\Models\EMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewTarrif extends Model
{
    use HasFactory;

     protected $table = "EMS_ZONE.dbo.Tariff";  //[EMS_ZONE].[dbo].[Tariff]

    protected $connection = 'zone_connection';

    public $timestamps = false;
}
