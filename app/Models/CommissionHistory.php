<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'commission_amount', 'commision_percent', 'amount_paid', 'acount_type', 'account_id', 'transaction_id'
    ];
}
