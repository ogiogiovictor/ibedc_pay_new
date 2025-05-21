<?php

namespace App\Models\NAC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContinueAccountCreation extends Model
{
    use HasFactory;

    protected $table = "continue_account_creations";

    protected $guarded = [];

     public function account()
    {
        return $this->belongsTo(AccoutCreaction::class, 'tracking_id', 'tracking_id');
    }
}
