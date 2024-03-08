<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Wallet\WalletUser;
use App\Models\VirtualAccount;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'pin',
        'status',
        'phone',
        'authority',
        'meter_no_primary'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function wallet(): HasOne {
        return $this->hasOne(WalletUser::class, "user_id");
    }

    public function virtualAccount() : HasOne {
        return $this->hasOne(VirtualAccount::class, 'user_id');
    }

    public static function userCount(): int {
        return self::count();
    }

    public static function userCountFormatted(): string {
        return number_format(self::count());
    }
    

}
