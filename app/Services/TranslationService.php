<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    /**
     * Translate text from default language to target language using OpenAI API
     */
    public function translate(string $text, string $targetLanguageCode, ?string $sourceLanguageCode = null, bool $isAdmin = false): ?string
    {
        // Check if translation is enabled based on user type
        if ($isAdmin) {
            if (! Setting::get('translation_enabled', false)) {
                Log::error('Translation disabled for admin', [
                    'user_type' => 'admin',
                    'setting' => 'translation_enabled',
                ]);

                return null;
            }
        } else {
            if (! Setting::get('translation_enabled_for_users', false)) {
                Log::error('Translation disabled for users', [
                    'user_type' => 'user',
                    'setting' => 'translation_enabled_for_users',
                ]);

                return null;
            }
        }

        // Get OpenAI API key
        $apiKey = Setting::get('openai_api_key');
        if (empty($apiKey)) {
            Log::error('OpenAI API key is not set', [
                'user_type' => $isAdmin ? 'admin' : 'user',
            ]);

            return null;
        }

        // Get model
        $model = Setting::get('openai_model', 'gpt-4o-mini');

        // Get source language code if not provided
        if ($sourceLanguageCode === null) {
            $defaultLanguage = Language::where('is_default', true)->where('is_active', true)->first();
            if (! $defaultLanguage) {
                Log::error('Default language not found', [
                    'user_type' => $isAdmin ? 'admin' : 'user',
                    'text' => mb_substr($text, 0, 100),
                ]);

                return null;
            }
            $sourceLanguageCode = $defaultLanguage->code;
        }

        // Get target language
        $targetLanguage = Language::where('code', $targetLanguageCode)->where('is_active', true)->first();
        if (! $targetLanguage) {
            Log::error('Target language not found', [
                'user_type' => $isAdmin ? 'admin' : 'user',
                'target_language_code' => $targetLanguageCode,
                'text' => mb_substr($text, 0, 100),
            ]);

            return null;
        }

        // Get language names for better translation
        $sourceLanguageName = Language::where('code', $sourceLanguageCode)->value('title') ?? $sourceLanguageCode;
        $targetLanguageName = $targetLanguage->title;

        // Don't translate if source and target are the same
        if ($sourceLanguageCode === $targetLanguageCode) {
            return $text;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a professional translator. Translate the given text from {$sourceLanguageName} to {$targetLanguageName}. Only return the translated text, nothing else. Do not add any explanations or comments.",
                    ],
                    [
                        'role' => 'user',
                        'content' => $text,
                    ],
                ],
                'temperature' => 0.3,
                'max_tokens' => 500,
            ]);

            // Log the response (always, even if failed)
            try {
                $responseJson = $response->json();
                $responseBody = $response->body();
            } catch (\Exception $e) {
                $responseJson = null;
                $responseBody = $response->body();
            }

            // Always log response, use error level if failed, warning if successful (to ensure visibility)
            $logLevel = $response->successful() ? 'warning' : 'error';
            Log::{$logLevel}('OpenAI API response received', [
                'status' => $response->status(),
                'status_text' => $response->reason(),
                'successful' => $response->successful(),
                'response_json' => $responseJson,
                'response_body' => $responseBody,
                'response_headers' => $response->headers(),
                'source_language' => $sourceLanguageCode,
                'target_language' => $targetLanguageCode,
                'target_language_name' => $targetLanguageName,
                'text' => mb_substr($text, 0, 200),
                'text_length' => strlen($text),
                'model' => $model,
                'user_type' => $isAdmin ? 'admin' : 'user',
                'api_key_set' => ! empty($apiKey),
                'api_key_preview' => ! empty($apiKey) ? substr($apiKey, 0, 10).'...' : null,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $translatedText = $result['choices'][0]['message']['content'] ?? null;

                if ($translatedText) {
                    return trim($translatedText);
                }

                // Log if translation text is empty
                Log::error('OpenAI API returned empty translation', [
                    'response' => $result,
                    'source_language' => $sourceLanguageCode,
                    'target_language' => $targetLanguageCode,
                    'text' => $text,
                ]);
            } else {
                // Response already logged above, but log error details separately
                Log::error('OpenAI API error - Translation failed', [
                    'status' => $response->status(),
                    'status_text' => $response->reason(),
                    'headers' => $response->headers(),
                    'body' => $responseBody,
                    'json' => $responseJson,
                    'source_language' => $sourceLanguageCode,
                    'target_language' => $targetLanguageCode,
                    'target_language_name' => $targetLanguageName,
                    'text' => $text,
                    'text_length' => strlen($text),
                    'model' => $model,
                    'user_type' => $isAdmin ? 'admin' : 'user',
                    'api_key_set' => ! empty($apiKey),
                    'api_key_preview' => ! empty($apiKey) ? substr($apiKey, 0, 10).'...' : null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Translation exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'source_language' => $sourceLanguageCode ?? null,
                'target_language' => $targetLanguageCode,
                'text' => $text,
            ]);
        }

        return null;
    }

    /**
     * Check if translation feature is enabled for admin
     */
    public function isEnabledForAdmin(): bool
    {
        return (bool) Setting::get('translation_enabled', false);
    }

    /**
     * Check if translation feature is enabled for users
     */
    public function isEnabledForUsers(): bool
    {
        return (bool) Setting::get('translation_enabled_for_users', false);
    }
}
