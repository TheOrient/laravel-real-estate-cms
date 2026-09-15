<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VersionCheck
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is logged in, check version
        if (Auth::check()) {
            $sessionVersion = $request->session()->get('user_version');
            $currentVersion = Auth::user()->version;

            // If version in session doesn't match or is not set, update it
            if ($sessionVersion !== $currentVersion) {
                // First login or version changed
                if ($sessionVersion && $sessionVersion !== $currentVersion) {
                    // Version changed (likely password changed), log out
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')
                        ->with('error', __('auth.session_expired'));
                } else {
                    // First login, set version
                    $request->session()->put('user_version', $currentVersion);
                }
            }
        }

        return $next($request);
    }
}