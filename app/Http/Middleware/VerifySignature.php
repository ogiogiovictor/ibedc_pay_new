<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         // Assuming 'Signature' is the header name
         $signature = $request->header('Signature');

         // Assuming 'request_ref' and 'app_secret' are parameters in the request
         $dataToHash = $request->input('request_ref').";CQcQrCeeLdmkP5ME";
         $hashedData = md5($dataToHash);
 
         if ($signature !== $hashedData) {
             return response()->json(['error' => 'Invalid signature'], 401);
         }

        return $next($request);
    }
}
