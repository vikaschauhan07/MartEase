<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Helpers\ProjectConstants;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateDriver
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('drivers')->check() && Auth::guard('drivers')->user()->status == ProjectConstants::USER_ACTIVE) {
            if(Auth::guard('drivers')->user()->step_completed == 4){
                return $next($request);
            } else {
                return ApiResponse::successResponse(null, "Complete your registration process.", ProjectConstants::UNAUTHENTICATED);
            }
        }
        return ApiResponse::errorResponse(null, "Uh-oh! It seems you've been logged out. Please log in again to continue using the app.", ProjectConstants::UNAUTHENTICATED);
    }
}
