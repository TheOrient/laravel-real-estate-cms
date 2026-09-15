<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use App\Models\Language;

class SetDefaultLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Resolution order:
        //   1. explicit ?lang= URL — crawlable language alternative
        //   2. session('user_locale') — set by the header switcher
        //   3. cookie('user_locale')  — persisted across sessions
        //   4. DB default language
        $userCode = $request->query('lang') ?? session('user_locale') ?? $request->cookie('user_locale');

        // If the visitor explicitly picked a locale, honour it even when
        // the languages table doesn't have a matching row — translations
        // come from lang/{code}/ files regardless of DB state.
        if ($userCode && in_array($userCode, ['tr', 'en'], true)) {
            App::setLocale($userCode);
            Lang::setLocale($userCode);

            $language = Language::cachedByCode($userCode);
            if ($language) {
                session(['current_language' => $language]);
            }

            return $next($request);
        }

        // No explicit pick → fall back to DB default.
        $language = Language::defaultCached();
        if ($language && ! $language->is_active) {
            $language = Language::runtimeList()
                ->first(fn (Language $item) => $item->is_active && $item->code === Config::get('app.default_language', 'tr'));
        }

        if (! $language) {
            App::setLocale(Config::get('app.default_language', 'tr'));
            Lang::setLocale(Config::get('app.default_language', 'tr'));
            return $next($request);
        }

        App::setLocale($language->code);
        Lang::setLocale($language->code);
        session(['current_language' => $language]);

        return $next($request);
    }
}
