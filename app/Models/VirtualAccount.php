<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CustomerAccount;

class VirtualAccount extends Model
{
    use HasFactory;

    protected $table = "virtual_accounts";

    protected $fillable = [
        'transaction_ref', 'account_no', 'contract_code', 'account_reference', 'account_name', 'customer_email', 'bank_name', 
        'bank_code', 'account_type', 'status', 'user_id'
    ];


    public function user(): BelongsTo {
        return $this->belongsTo(User::class, "id");
    }

     public function customer(): BelongsTo {
        return $this->belongsTo(CustomerAccount::class, "id");
    }
}
