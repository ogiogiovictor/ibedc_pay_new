<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IBEDCPayLogs extends Model
{
    use HasFactory;

    protected $table = "ibedcpaylogs";
    protected $guarded = [];
}
