<?php

namespace App\Models\MIDDLEWARE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stores extends Model
{
    use HasFactory;

     use HasFactory;

    protected $table = "MSMS_NEW.restored.store_tbls";

    protected $connection = 'msms';

    public $timestamps = false;

}
