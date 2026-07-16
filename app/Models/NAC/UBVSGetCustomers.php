<?php

namespace App\Models\NAC;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UBVSGetCustomers extends Model
{
    use HasFactory;

    protected $table = "MIDDLEWARE.dbo.Transactions";

    protected $connection = 'ubvs';

    //primary key should be transaction_id
    protected $primaryKey = 'transaction_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];
}
