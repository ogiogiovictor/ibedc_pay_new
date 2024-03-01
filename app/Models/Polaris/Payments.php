<?php

namespace App\Models\Polaris;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use HasFactory;

    protected $table = "polaris_payments";

    protected $fillable = [
        'request_ref', 'request_type', 'requester', 'transaction_type', 'amount', 'status', 'provider', 
        'transaction_ref', 'VirtualAccount', 'VirtualAccountName', 'Narration', 'SenderAccountNumber', 
        'SenderAccountName', 'SenderBankName', 'account_number', 'transaction_date', 'customer_ref',
        'customer_firstname', 'customer_surname', 'customer_email', 'customer_mobile_no', 'Hash', 'used'
    ];

   
}
