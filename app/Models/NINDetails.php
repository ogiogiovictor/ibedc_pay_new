<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NINDetails extends Model
{
    use HasFactory;

    protected $table = "nin_details";

    protected $fillable = ['nin', 'payload'];

    protected $casts = [
        'payload' => 'array', // or 'json'
    ];
}
