<?php

namespace App\Models\Wallet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Wallet\WalletHistory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class WalletUser extends Model
{
    use HasFactory;

    protected $table = "wallet_users";

    protected $fillable = [
        'user_id', 'wallet_amount' , 'commission_amount'
    ];


    public function users(): BelongsTo {
        return $this->belongsTo(User::class, "id");
    }

    public function myhistory(): HasMany {
        return $this->hasMany(WalletHistory::class, "user_id", "user_id");
    }
}
