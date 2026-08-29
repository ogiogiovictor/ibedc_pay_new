<?php

namespace App\Models\MIDDLEWARE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Metering extends Model
{
    use HasFactory;

     protected $table = "MIDDLEWARE.mdwibedc.company";

    protected $connection = 'msms';

    public $timestamps = false;
}
