<?php

namespace App\Models\EMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneCustomers extends Model
{
    use HasFactory;

    protected $table = "EMS_ZONE.dbo.CustomerNew";

    protected $connection = 'zone_connection';

    public $timestamps = false;

    public function validatecustomer(){
        return $this->where("AccountNo", $this->AccountNo)->first();
    }
}
