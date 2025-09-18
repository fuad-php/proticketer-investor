<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Skip 2FA check if user doesn't have 2FA enabled
        if (!$user || !$user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        // Skip 2FA check if already verified in this session
        if (session('2fa_verified')) {
            return $next($request);
        }

        // Skip 2FA check for 2FA-related routes
        if ($request->routeIs('2fa.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        // Redirect to 2FA verification
        return redirect()->route('2fa.verify');
    }
}
