<?php

namespace App\Models\EMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessUnit extends Model
{
    use HasFactory;

    protected $table = "EMS_ZONE.dbo.BusinessUnit";

    protected $connection = 'zone_connection';

    public $timestamps = false;

    protected $primaryKey = 'BUID';   // use the actual PK column
    public $incrementing = false;     // disable auto-increment
    protected $keyType = 'string';    // or 'int' if BUID is numeric
}
