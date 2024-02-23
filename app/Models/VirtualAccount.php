<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VirtualAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_ref', 'account_no', 'contract_code', 'account_reference', 'account_name', 'customer_email', 'bank_name', 
        'bank_code', 'account_type', 'status', 'user_id'
    ];


    public function users(): BelongsTo {
        return $this->belongsTo(User::class, "id", "user_id");
    }
}
