<?php

namespace App\Models\NAC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiddlewareWarehouse extends Model
{
    use HasFactory;

     protected $table = "Middleware.dbo.transactions";

    protected $connection = 'middleware_data_warehouse';

   // public $timestamps = false;

    protected $primaryKey = 'TXMessage';   // use the actual PK column
   // public $incrementing = false;     // disable auto-increment
   // protected $keyType = 'string';    // or 'int' if BUID is numeric
}
