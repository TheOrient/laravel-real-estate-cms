<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CaptchaService
{
    /**
     * Verify CAPTCHA response
     */
    public function verify(string $response, string $formType = 'contact'): bool
    {
        $provider = Setting::get('captcha_provider', 'none');

        // Check if CAPTCHA is enabled for this form
        $enabledKey = "captcha_enabled_{$formType}";
        if (!Setting::get($enabledKey, true)) {
            return true; // CAPTCHA disabled for this form
        }

        if ($provider === 'none' || empty($response)) {
            return $provider === 'none'; // Pass if disabled, fail if enabled but no response
        }

        return match ($provider) {
            'recaptcha_v2' => $this->verifyRecaptchaV2($response),
            'recaptcha_v2_invisible' => $this->verifyRecaptchaV2($response),
            'recaptcha_v3' => $this->verifyRecaptchaV3($response),
            'turnstile' => $this->verifyTurnstile($response),
            default => true,
        };
    }

    /**
     * Verify reCAPTCHA v2 response
     */
    protected function verifyRecaptchaV2(string $response): bool
    {
        $secretKey = Setting::get('recaptcha_v2_secret_key');

        if (empty($secretKey)) {
            Log::warning('reCAPTCHA v2 secret key not configured');
            return true; // Don't block if not configured
        }

        try {
            $result = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $response,
                'remoteip' => request()->ip(),
            ]);

            $data = $result->json();

            Log::info('reCAPTCHA v2 verification', [
                'success' => $data['success'] ?? false,
                'error_codes' => $data['error-codes'] ?? [],
            ]);

            return $data['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA v2 verification failed: ' . $e->getMessage());
            return true; // Don't block on error
        }
    }

    /**
     * Verify reCAPTCHA v3 response
     */
    protected function verifyRecaptchaV3(string $response): bool
    {
        $secretKey = Setting::get('recaptcha_v3_secret_key');
        $threshold = (float) Setting::get('recaptcha_v3_score_threshold', 0.5);

        if (empty($secretKey)) {
            Log::warning('reCAPTCHA v3 secret key not configured');
            return true;
        }

        try {
            $result = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $response,
                'remoteip' => request()->ip(),
            ]);

            $data = $result->json();

            if (!($data['success'] ?? false)) {
                Log::warning('reCAPTCHA v3 verification failed', [
                    'error_codes' => $data['error-codes'] ?? [],
                ]);
                return false;
            }

            $score = $data['score'] ?? 0;

            Log::info('reCAPTCHA v3 verification', [
                'score' => $score,
                'threshold' => $threshold,
                'action' => $data['action'] ?? 'unknown',
                'passed' => $score >= $threshold,
            ]);

            return $score >= $threshold;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA v3 verification failed: ' . $e->getMessage());
            return true;
        }
    }

    /**
     * Verify Cloudflare Turnstile response
     */
    protected function verifyTurnstile(string $response): bool
    {
        $secretKey = Setting::get('turnstile_secret_key');

        if (empty($secretKey)) {
            Log::warning('Turnstile secret key not configured');
            return true;
        }

        try {
            $result = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secretKey,
                'response' => $response,
                'remoteip' => request()->ip(),
            ]);

            $data = $result->json();

            Log::info('Turnstile verification', [
                'success' => $data['success'] ?? false,
                'error_codes' => $data['error-codes'] ?? [],
            ]);

            return $data['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('Turnstile verification failed: ' . $e->getMessage());
            return true;
        }
    }

    /**
     * Get current CAPTCHA provider
     */
    public function getProvider(): string
    {
        return Setting::get('captcha_provider', 'none');
    }

    /**
     * Check if CAPTCHA is enabled for a specific form
     */
    public function isEnabled(string $formType): bool
    {
        $provider = $this->getProvider();
        if ($provider === 'none') {
            return false;
        }

        $enabledKey = "captcha_enabled_{$formType}";
        return (bool) Setting::get($enabledKey, true);
    }

    /**
     * Get site key for current provider
     */
    public function getSiteKey(): ?string
    {
        $provider = $this->getProvider();

        return match ($provider) {
            'recaptcha_v2' => Setting::get('recaptcha_v2_site_key'),
            'recaptcha_v2_invisible' => Setting::get('recaptcha_v2_site_key'),
            'recaptcha_v3' => Setting::get('recaptcha_v3_site_key'),
            'turnstile' => Setting::get('turnstile_site_key'),
            default => null,
        };
    }
}
