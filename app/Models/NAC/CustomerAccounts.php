<?php

namespace App\Models\NAC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAccounts extends Model
{
    use HasFactory;

    protected $table = "customer_accounts";

      protected $guarded = ['id'];

        public function account()
    {
        return $this->belongsTo(AccoutCreaction::class, 'tracking_id', 'tracking_id');
    }
}
