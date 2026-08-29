<?php

namespace App\Models\MIDDLEWARE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRecords extends Model
{
    use HasFactory;

    protected $table = "MSMS_NEW.restored.payment_records";

    protected $connection = 'msms';

    public $timestamps = false;
}