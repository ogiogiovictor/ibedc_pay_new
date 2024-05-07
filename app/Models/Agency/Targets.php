<?php

namespace App\Models\Agency;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Targets extends Model
{
    use HasFactory;

    protected $table = "targets";

    protected $fillable = [
        'agency_id', 'year', 'month', 'target_amount'
    ];
}
