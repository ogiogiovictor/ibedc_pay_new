<?php

namespace App\Models\NAC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingAccountCreation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'upload_houses' => 'array',
        'account' => 'array',
        'user' => 'array',
    ];
}
