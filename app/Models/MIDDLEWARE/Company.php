<?php

namespace App\Models\MIDDLEWARE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = "MIDDLEWARE.mdwibedc.company";

    protected $connection = 'middleware1';

    public $timestamps = false;
}
