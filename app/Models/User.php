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
use App\Enums\RoleEnum;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Agency\Agents;
use App\Models\Transactions\PaymentTransactions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes, Notifiable, HasRoles;

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
        'meter_no_primary',
        'agency',
        'account_type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pin'
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

    public function isAdmin()
    {
        return $this->authority === RoleEnum::admin()->value; 
    }

    public function isSuperAdmin()
    {
        return $this->authority === RoleEnum::super_admin()->value;
    }

    public function isManager()
    {
        return $this->authority === RoleEnum::manager()->value;
    }

    public function isSupervisor()
    {
        return $this->authority === RoleEnum::supervisor()->value;
    }

    public function isCustomer()
    {
        return $this->authority === RoleEnum::customer()->value;
    }

    public function isAgent()
    {
        return $this->authority === RoleEnum::agent()->value;
    }

    public function isPaymentChannel()
    {
        return $this->authority === RoleEnum::payment_channel()->value;
    }

    public function isAgencyAdmin()
    {
        return $this->authority === RoleEnum::agency_admin()->value;
    }

    public function agency()
    {
        return $this->belongsTo(Agents::class);  // This is the Agency not Agents
    }

    public function PaymentTransactions() {
        return $this->hasMany(PaymentTransactions::class, "user_id");
    }

    

}
