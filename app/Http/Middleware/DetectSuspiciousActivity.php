<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DetectSuspiciousActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

     // Maximum allowed requests per minute
    protected $requestLimit = 100; 
    protected $blockDuration = 5; // in minutes


    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $path = $request->path();
        $uri = $request->getRequestUri();

        // 1️⃣ Check if IP is already blocked
        if (Cache::has("blocked_ip_{$ip}")) {
            Log::warning("Blocked IP tried to access: {$ip}");
            abort(429, "Too many requests. You are temporarily blocked.");
        }

        // 2️⃣ Count requests per IP per minute
        $key = "req_count_{$ip}";
        $count = Cache::increment($key);
        if ($count === 1) {
            Cache::put($key, 1, now()->addMinute());
        }

        if ($count > $this->requestLimit) {
            Cache::put("blocked_ip_{$ip}", true, now()->addMinutes($this->blockDuration));
            Log::alert("🚨 Potential DDoS attack: IP {$ip} exceeded {$this->requestLimit} requests/min");
            abort(429, "Too many requests. You are temporarily blocked.");
        }

        // 3️⃣ Detect suspicious patterns (SQL Injection / XSS attempts)
        $patterns = ['/(\bunion\b|\bselect\b|\bdrop\b|\binsert\b)/i', '/<script>/i'];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $uri)) {
                Log::alert("🚨 Suspicious request detected from {$ip}: {$uri}");
            }
        }

        return $next($request);

        
       // return $next($request);
    }
}
