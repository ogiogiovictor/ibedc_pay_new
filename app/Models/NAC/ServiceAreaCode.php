<?php

namespace App\Models\NAC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceAreaCode extends Model
{
    use HasFactory;

    protected $table = "area_code_service_center";

      // Set the actual primary key column
    protected $primaryKey = 'SN';

    // If the primary key is not auto-incrementing
    public $incrementing = false;

    // If the key is not an integer (e.g., string)
    protected $keyType = 'string';

     public $timestamps = false;

}
