<?php

namespace App\Services;

use App\Models\IBEDCPayLogs;
use Illuminate\Support\Facades\Auth;

class IbedcPayLogService
{
    public static function create(array $data): IBEDCPayLogs
    {
         return IBEDCPayLogs::create([
            'user_id'    => $data['user_id'] ?? Auth::id(),
            'module'     => $data['module'] ?? null,
            'comment'    => $data['comment'] ?? null,
            'user_email' => $data['user_email'] ?? (Auth::check() ? Auth::user()->email : null),
            'type'       => $data['type'] ?? null,
            'module_id'  => $data['module_id'] ?? null,
            'status'     => $data['status'] ?? null,
        ]);
    }
}
