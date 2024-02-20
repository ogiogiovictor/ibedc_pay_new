<?php

namespace App\Models\ECMI;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcmiTransactions extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.PaymentTransaction";

    protected $connection = 'ecmi_prod';

    protected $primaryKey = 'transref';

    public $timestamps = false;
}
