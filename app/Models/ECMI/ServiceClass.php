<?php

namespace App\Models\ECMI;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceClass extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.ServiceClass";

    protected $connection = 'ecmi_prod';

    public $timestamps = false;
}
