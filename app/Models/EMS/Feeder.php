<?php

namespace App\Models\EMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feeder extends Model
{
    use HasFactory;

    protected $table = "EMS_ZONE.dbo.FeederPillars";

    protected $connection = 'zone_connection';


    public $timestamps = false;
}
