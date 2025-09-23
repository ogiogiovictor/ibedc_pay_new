<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCenterArea extends Model
{
    use HasFactory;

    protected $table = "area_code_service_center";

    protected $guarded = [];

    //   public $incrementing = false;
    // public $timestamps = false;
    // protected $primaryKey = null; // No primary key
}
