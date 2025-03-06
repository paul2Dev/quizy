<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateLastLogin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            $user = Auth::user();

            // Update last_login_at if it's not already updated recently
            if (!$user->last_login_at || now()->diffInMinutes($user->last_login_at) > 1) {
                $user->update(['last_login_at' => now()]);
            }
        }

        return $next($request);
    }
}
