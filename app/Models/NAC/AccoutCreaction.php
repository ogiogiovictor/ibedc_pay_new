<?php

namespace App\Models\NAC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccoutCreaction extends Model
{
    use HasFactory;

    protected $table = "account_creations";

    //protected $guarded = [];
    protected $guarded = ['id'];

    public function continuation()
    {
        return $this->hasOne(ContinueAccountCreation::class, 'tracking_id', 'tracking_id');
    }

     public function uploadinformation()
    {
        return $this->hasOne(UploadAccountCreation::class, 'tracking_id', 'tracking_id');
    }

     public function caccounts()
    {
        return $this->hasOne(CustomerAccounts::class, 'tracking_id', 'tracking_id');
    }

    public function uploadedPictures()
    {
        return $this->hasMany(UploadHouses::class, 'tracking_id', 'tracking_id');
    }

}
