<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class HandleAuthenticationErrors
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (AuthenticationException $e) {
            return response()->json([
                'message' => 'Bạn cần đăng nhập để thực hiện hành động này',
                'error' => 'Unauthenticated'
            ], 401);
        }
    }
} 