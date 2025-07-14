<?php

namespace App\Models\NAC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadHouses extends Model
{
    use HasFactory;

    protected $table = "upload_houses";

    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(AccoutCreaction::class, 'tracking_id', 'tracking_id');
    }

    public function customer()
    {
        return $this->hasOne(AccoutCreaction::class, 'tracking_id', 'tracking_id');
    }
}
