<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * User-side AI helper — currently exposes the listing description
 * generator so the multi-step "ilan ver" flow can call it from step 2
 * without leaning on the admin-only endpoint.
 */
class AIController extends Controller
{
    public function __construct(protected AIService $ai) {}

    public function generateListingDescription(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'price'    => ['nullable', 'string', 'max:64'],
            'city'     => ['nullable', 'string', 'max:128'],
            'district' => ['nullable', 'string', 'max:128'],
            'neighborhood' => ['nullable', 'string', 'max:128'],
            'notes'    => ['nullable', 'string', 'max:1000'],
            'language' => ['nullable', 'in:tr,en'],
        ]);

        if (! $this->ai->isConfigured()) {
            return response()->json([
                'ok'    => false,
                'error' => __('admin/ai.not_configured'),
            ], 503);
        }

        $language = $data['language'] ?? app()->getLocale();
        $result   = $this->ai->generateListingDescription($data, $language);

        if (is_array($result) && isset($result['error'])) {
            return response()->json(['ok' => false, 'error' => $result['error']], 502);
        }

        return response()->json([
            'ok'   => true,
            'text' => trim((string) $result),
        ]);
    }
}
