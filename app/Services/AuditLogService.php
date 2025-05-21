<?php

namespace App\Services;

use App\Models\AuditLogs;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request as iRequest;

class AuditLogService
{
    /**
     * Log an action in the audit logs.
     *
     * @param  string  $action  The action performed (e.g., "User Login", "Failed Login Attempt").
     * @param  string  $authority  The Authority (e.g., "User", "Admin", "Payment Channel").
     * @param  string  $details  Additional details about the action.
     * @param  int|null  $userId  The ID of the user who performed the action (nullable for guests).
     * @param  int|null  $statusCode  The HTTP status code of the response.
     * @return void
     */
    public static function logAction($action, $authority, $details, $userId = null, $statusCode = null)
    {
        AuditLogs::create([
            'user_id' => $userId,
            'ip_address' => Request::ip(),
            'url' => Request::fullUrl(),
            'action' => $action,
            'auhority' => $authority,
            'method' => Request::method(),
            'user_agent' => Request::header('User-Agent'), // Captures the User-Agent
            'payload' => json_encode(Request::all()), // Logs request payload
            'status_code' => $statusCode,
            'response' => $details,
        ]);
    }
}
