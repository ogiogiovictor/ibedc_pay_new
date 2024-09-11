<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedVirtualAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'payload' => 'collection',
    ];
}
