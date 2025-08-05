<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Logs\AppLog;
use Illuminate\Support\Facades\Auth;


class TerminatingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {

        $user = isset(Auth::user()->id) ? Auth::user()->id : null;

        $getresonseContent = $response->getContent();
        $responseContent = substr($getresonseContent, 0, 4294967295); // Adjust the length as needed
    
        // Convert array payload to JSON string
        $payload = json_encode($request->toArray());

        //Data points to captures
        $data = [
            'user_id' => $user,
            'ip_address' => $request->ip(),
            'ajax'  =>  $request->ajax(),
            'url'   =>  $request->fullUrl(),
            'method'    =>  $request->method(),
            'user_agent'    =>  $request->userAgent(),
            'payload'   =>  $payload,
            'status_code'  =>  method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
            'response'  => $responseContent,
            
        ];

       // AppLog::create($data);
        //Log::info(__METHOD__ . ' - ' . $request->toArray());
    }
}
