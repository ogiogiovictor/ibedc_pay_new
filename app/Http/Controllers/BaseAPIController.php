<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseAPIController extends Controller
{
    public function sendSuccess($payload = [], string $message = 'success', int $code = Response::HTTP_OK){

        return self::respond(true, $payload, $message, $code);

    }

    public function sendError($payload = [], string $message = 'error', int $code = Response::HTTP_INTERNAL_SERVER_ERROR){
        return self::respond(false, $payload, $message, $code);
    }

    protected static function respond(bool $success, $payload = [], string $message, int $code)
    {
     return response()->json(['success' => $success, 'message' => $message, 'payload' => $payload],  $code);
    }
}
