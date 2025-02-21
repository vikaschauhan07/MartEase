<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Helpers\ProjectConstants;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSeller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('seller')->check() && Auth::guard('seller')->user()) {
            if(Auth::guard('seller')->user()->status == ProjectConstants::USER_ACTIVE){
                return $next($request);
            }
            return ApiResponse::errorResponse(null, "Your account is not active. Please contact admin.", ProjectConstants::UNAUTHENTICATED);
        }
        return ApiResponse::errorResponse(null, "Uh-oh! It seems you've been logged out. Please log in again to continue using the app.", ProjectConstants::UNAUTHENTICATED);
    }
}
