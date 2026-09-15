<?php

namespace App\Http\Controllers;

use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TranslationController extends Controller
{
    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Translate text from default language to target language
     */
    public function translate(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string',
            'target_language_code' => 'required|string',
        ]);

        $text = $request->input('text');
        $targetLanguageCode = $request->input('target_language_code');

        // Check if user is admin or regular user
        $isAdmin = $request->user() && $request->user()->isAdmin();

        Log::error('Translation request received', [
            'user_id' => $request->user()?->id,
            'is_admin' => $isAdmin,
            'target_language_code' => $targetLanguageCode,
            'text_length' => strlen($text),
            'text_preview' => mb_substr($text, 0, 100),
        ]);

        $translatedText = $this->translationService->translate($text, $targetLanguageCode, null, $isAdmin);

        if ($translatedText === null) {
            Log::error('Translation failed in controller', [
                'user_id' => $request->user()?->id,
                'is_admin' => $isAdmin,
                'target_language_code' => $targetLanguageCode,
                'text_length' => strlen($text),
                'text_preview' => mb_substr($text, 0, 100),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('admin/general.translation_failed'),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'translated_text' => $translatedText,
        ]);
    }
}
