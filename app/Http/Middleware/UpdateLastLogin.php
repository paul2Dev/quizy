<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

            $lastLogin = $user->last_login_at ? Carbon::parse($user->last_login_at) : null;

            // Update last_login_at if it's not already updated recently
            if ($lastLogin) {
                $user->update(['last_login_at' => now()]);
            }
        }

        return $next($request);
    }
}
