<?php

namespace App\Models\Logs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppAuthorization extends Model
{
    use HasFactory;

    protected  $table = "app_authorizations";

    protected $fillable = [
        'appSecret',
        'appToken',
        'appName',
        'status',
    ];
}
