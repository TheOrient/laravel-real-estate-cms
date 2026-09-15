<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Persists the visitor's locale choice in the session and bounces
 * them back to the previous page. SetDefaultLanguage middleware
 * picks the value up on the next request.
 */
class LocaleController extends Controller
{
    public function switch(Request $request, string $code): RedirectResponse
    {
        // Accept any locale that has both a translation directory on
        // disk AND (optionally) a row in `languages`. This lets the
        // header pill switch to English even if the operator hasn't
        // run the activate-english migration yet — translations come
        // from the lang/en/ files, the DB row is decorative.
        $supported = ['tr', 'en'];
        if (! in_array($code, $supported, true)) {
            return $this->bounce($request);
        }

        // Best-effort DB lookup so other consumers (session-bound
        // language model) still get a real row when available.
        $language = Language::where('code', $code)->first();

        session([
            'user_locale'      => $code,
            'current_language' => $language,
        ]);
        cookie()->queue('user_locale', $code, 60 * 24 * 365);

        return $this->bounce($request);
    }

    /**
     * Send the visitor back where they came from, defaulting to home.
     */
    protected function bounce(Request $request): RedirectResponse
    {
        $back = $request->headers->get('referer');
        return $back ? redirect($back) : redirect()->route('home');
    }
}
