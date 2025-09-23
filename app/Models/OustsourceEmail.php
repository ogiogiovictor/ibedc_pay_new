<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OustsourceEmail extends Model
{
    use HasFactory;

    protected $table = "Outsourced_Mails";

    protected $fillable = ['EMAIL_ADDRESS', 'CODE'];

    protected $primaryKey = 'EMAIL_ADDRESS';
    public $incrementing = false;             // no auto-incrementing ID
    protected $keyType = 'string';            // since it's an email (string)
    public $timestamps = false;               // disable created_at / updated_at
}
