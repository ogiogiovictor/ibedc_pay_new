<?php

namespace App\Models\ECMI;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcmiPayments extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.Transactions";

    protected $connection = 'ecmi_prod';

    protected $primaryKey = 'TransactionNo';

    public $timestamps = false;
}
