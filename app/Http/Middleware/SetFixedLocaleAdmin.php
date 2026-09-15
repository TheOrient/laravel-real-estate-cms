<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class SetFixedLocaleAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Admin panel locale resolution: the operator's pick (session/cookie)
        // wins so a Turkish editor can flip to English mid-session for
        // proofing translations. Falls back to the configured fixed
        // admin locale, then to the app default — preserves existing
        // behaviour for installs that never set a session locale.
        $code = session('user_locale')
            ?? $request->cookie('user_locale')
            ?? config('app.fixed_locale_admin')
            ?? config('app.locale', 'tr');

        if ($code) {
            App::setLocale($code);
            Lang::setLocale($code);
        }

        if ($request->route()) {
            $request->route()->setParameter('lang', $code);
        }

        return $next($request);
    }
}
