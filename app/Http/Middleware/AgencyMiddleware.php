<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleEnum;

class AgencyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if($user->authority == (RoleEnum::agency_admin()->value )) {
          //redirect to agency dashboard
          return $next($request);
        } 

        abort(403, 'Unathorized action. No Access to Resource');

        
    }
}
