<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Lightweight JSON endpoint the admin / agent listing form calls to
 * generate a description with AI. Returns:
 *   { ok: true,  text: "...generated..." }
 *   { ok: false, error: "..." }
 *
 * Rate-limited via the `throttle:ai` middleware alias (registered in
 * routes/web.php) so a stuck UI can't burn a thousand tokens.
 */
class AIController extends Controller
{
    public function __construct(protected AIService $ai) {}

    public function generateListingDescription(Request $request): JsonResponse
    {
        $data = $request->validate([
            'language'     => 'nullable|string|in:tr,en',
            'title'        => 'nullable|string|max:255',
            'category'     => 'nullable|string|max:255',
            'price'        => 'nullable|string|max:64',
            'city'         => 'nullable|string|max:128',
            'district'     => 'nullable|string|max:128',
            'neighborhood' => 'nullable|string|max:128',
            'notes'        => 'nullable|string|max:1000',
            'features'     => 'nullable|array',
            'features.*'   => 'string|max:255',
        ]);

        if (! $this->ai->isConfigured()) {
            return response()->json([
                'ok'    => false,
                'error' => __('admin/ai.not_configured'),
            ], 503);
        }

        $language = $data['language'] ?? app()->getLocale();
        $result = $this->ai->generateListingDescription($data, $language);

        if (is_array($result) && isset($result['error'])) {
            return response()->json(['ok' => false, 'error' => $result['error']], 502);
        }

        return response()->json([
            'ok'   => true,
            'text' => $result,
        ]);
    }
}
