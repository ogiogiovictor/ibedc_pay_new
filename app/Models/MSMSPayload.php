<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MSMSPayload extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = "msms_payloads";

    protected $casts = [
        'content' => 'json', // or 'json'
    ];
}
