<?php

namespace App\Models\Logs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppLog extends Model
{
    use HasFactory;

    protected $table = "app_logs";

    protected $cast = [
        'payload' => 'collection'
    ];

    protected $fillable = [
        'user_id', 'ip_address', 'ajax', 'url', 'method', 'user_agent', 'payload', 'status_code', 'response'
    ];
}
