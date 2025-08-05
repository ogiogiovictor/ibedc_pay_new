<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Models\Wallet\WalletUser;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\VirtualAccount;



class CustomerAccount extends Authenticatable
{
   use HasApiTokens, HasFactory, Notifiable;

    protected $table = "login_customer_accounts";

    protected $guarded = [];

    
    protected $hidden = ['password'];

     public function wallet(): HasOne {
        return $this->hasOne(WalletUser::class, "user_id");
    }

    public function virtualAccount() : HasOne {
        return $this->hasOne(VirtualAccount::class, 'user_id');
    }

}
