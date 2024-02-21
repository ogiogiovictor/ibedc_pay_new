<?php

namespace App\Models\EMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZonePayments extends Model
{
    use HasFactory;

    protected $table = "EMS_ZONE.dbo.Payments";

    protected $connection = 'zone_connection';

    public $timestamps = false;
}