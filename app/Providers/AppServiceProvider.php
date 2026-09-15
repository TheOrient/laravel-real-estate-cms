<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // we shoul use bootstrap four pagination in admin panel
        if (request()->is('admin/*')) {
            Paginator::useBootstrapFour();
        }

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email');
            $identity = is_string($email) ? mb_strtolower(trim($email)) : '';

            return [
                Limit::perMinute(max(1, (int) config('auth.login_rate_limit')))
                    ->by('login-account:'.hash('sha256', $identity.'|'.$request->ip())),
                Limit::perMinute(max(1, (int) config('auth.login_ip_rate_limit')))
                    ->by('login-ip:'.$request->ip()),
            ];
        });

        // Mail delivery is configured through .env / config/mail.php.
        try {
            if (\Schema::hasTable('settings')) {
                // Site URL
                $siteUrl = \App\Models\Setting::get('site_url');
                if (!empty($siteUrl)) {
                    config(['app.url' => $siteUrl]);
                }

                // CAPTCHA Configuration
                $captchaProvider = \App\Models\Setting::get('captcha_provider', 'none');

                if ($captchaProvider === 'recaptcha_v2') {
                    $siteKey = \App\Models\Setting::get('recaptcha_v2_site_key');
                    $secretKey = \App\Models\Setting::get('recaptcha_v2_secret_key');

                    if ($siteKey && $secretKey) {
                        config([
                            'services.recaptcha.site_key' => $siteKey,
                            'services.recaptcha.secret' => $secretKey,
                            'recaptcha.api_site_key' => $siteKey,
                            'recaptcha.api_secret_key' => $secretKey,
                            'recaptcha.version' => 'v2',
                        ]);
                    }
                } elseif ($captchaProvider === 'recaptcha_v3') {
                    $siteKey = \App\Models\Setting::get('recaptcha_v3_site_key');
                    $secretKey = \App\Models\Setting::get('recaptcha_v3_secret_key');

                    if ($siteKey && $secretKey) {
                        config([
                            'services.recaptcha.site_key' => $siteKey,
                            'services.recaptcha.secret' => $secretKey,
                            'recaptcha.api_site_key' => $siteKey,
                            'recaptcha.api_secret_key' => $secretKey,
                            'recaptcha.version' => 'v3',
                            'recaptcha.score_threshold' => \App\Models\Setting::get('recaptcha_v3_score_threshold', 0.5),
                        ]);
                    }
                } elseif ($captchaProvider === 'turnstile') {
                    $siteKey = \App\Models\Setting::get('turnstile_site_key');
                    $secretKey = \App\Models\Setting::get('turnstile_secret_key');

                    if ($siteKey && $secretKey) {
                        config([
                            'services.turnstile.site_key' => $siteKey,
                            'services.turnstile.secret' => $secretKey,
                            'turnstile.site_key' => $siteKey,
                            'turnstile.secret_key' => $secretKey,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail if settings table doesn't exist yet (during migration)
            // This is normal during installation
        }
    }
}
