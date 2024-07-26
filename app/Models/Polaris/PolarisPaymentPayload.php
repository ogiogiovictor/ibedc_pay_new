<?php

namespace App\Models\Polaris;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolarisPaymentPayload extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'meter_no',
        'account_no',
        'payload',
    ];

    protected $casts = [
        'payload' => 'collection',
    ];
}
