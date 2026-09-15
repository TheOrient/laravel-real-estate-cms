<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * AIService — small wrapper over OpenAI / Anthropic chat APIs.
 *
 * Goals:
 *   - One method (generateListingDescription) the rest of the app calls
 *     so prompt engineering lives in one place.
 *   - Provider selection at runtime via settings; keys may come from
 *     either the settings table or .env (settings wins).
 *   - Fails soft — returns a meaningful error string instead of throwing
 *     into the request, so admin forms can show "AI unavailable" without
 *     breaking the save flow.
 */
class AIService
{
    public const PROVIDER_OPENAI = 'openai';

    public const PROVIDER_ANTHROPIC = 'anthropic';

    /** Groq — free, fast, OpenAI-compatible API. No credit card required.
     *  Get a key at https://console.groq.com/keys */
    public const PROVIDER_GROQ = 'groq';

    /**
     * Public entry point: build a real-estate listing description from
     * structured property data. Returns the generated text on success or
     * an associative array {error: "…"} when the provider isn't reachable.
     */
    public function generateListingDescription(array $context, string $language = 'tr')
    {
        $provider = $this->activeProvider();
        $key = $this->apiKey($provider);
        $models = $this->modelCandidates($provider);

        if (! $key) {
            return ['error' => "AI provider [{$provider}] has no API key configured."];
        }

        [$systemPrompt, $userPrompt] = $this->buildListingPrompt($context, $language);

        try {
            return match ($provider) {
                self::PROVIDER_ANTHROPIC => $this->callAnthropic($key, $models[0], $systemPrompt, $userPrompt),
                self::PROVIDER_GROQ => $this->callOpenAICompatible(
                    $key, $models, $systemPrompt, $userPrompt,
                    'https://api.groq.com/openai/v1/chat/completions'
                ),
                default => $this->callOpenAICompatible(
                    $key, $models, $systemPrompt, $userPrompt,
                    'https://api.openai.com/v1/chat/completions'
                ),
            };
        } catch (\Throwable $e) {
            Log::error('AIService failure', ['provider' => $provider, 'error' => $e->getMessage()]);

            return ['error' => __('admin/ai.request_failed')];
        }
    }

    /**
     * General chat completion used by the on-site chatbot. Same
     * provider selection rules as listing description, but the prompt
     * is built by the caller (lets us route domain-specific behaviour
     * — search assistant, support, …).
     *
     * @param  array<int, array{role:string, content:string}>  $messages
     * @return string|array<string,string>
     */
    public function chat(array $messages, ?string $systemOverride = null)
    {
        $provider = $this->activeProvider();
        $key = $this->apiKey($provider);
        $models = $this->modelCandidates($provider);

        if (! $key) {
            return ['error' => __('admin/ai.not_configured')];
        }

        $system = $systemOverride ?? $this->defaultChatSystemPrompt();
        $messages = array_slice(array_values(array_filter($messages, function ($message) {
            return is_array($message)
                && in_array($message['role'] ?? null, ['user', 'assistant'], true)
                && is_string($message['content'] ?? null)
                && trim($message['content']) !== '';
        })), -12);

        try {
            return match ($provider) {
                self::PROVIDER_ANTHROPIC => $this->callAnthropicChat($key, $models[0], $system, $messages),
                self::PROVIDER_GROQ => $this->callOpenAICompatibleChat(
                    $key, $models, $system, $messages,
                    'https://api.groq.com/openai/v1/chat/completions'
                ),
                default => $this->callOpenAICompatibleChat(
                    $key, $models, $system, $messages,
                    'https://api.openai.com/v1/chat/completions'
                ),
            };
        } catch (\Throwable $e) {
            Log::error('AIService chat failure', ['provider' => $provider, 'error' => $e->getMessage()]);

            return ['error' => __('admin/ai.request_failed')];
        }
    }

    /**
     * Brand-aware default system prompt for the chatbot.
     *
     * IMPORTANT: This is a single-office site, not a marketplace.
     * Visitors CANNOT register, log in, or post listings themselves —
     * the office is the only publisher. Guidance is deliberately
     * narrowed so the assistant never suggests "sign up", "list your
     * property", "create an account" or similar marketplace flows.
     * Instead, everything routes to office contact channels.
     */
    protected function defaultChatSystemPrompt(): string
    {
        $site = trim((string) get_setting('site_title', config('app.name'))) ?: (string) config('app.name');
        $phone = trim((string) get_setting('contact_phone', ''));
        $email = trim((string) get_setting('contact_email', ''));
        $locale = app()->getLocale() === 'en' ? 'en' : 'tr';

        // E-posta ayarı boşsa asistan e-posta adresi vermez; yalnız telefon.
        $parts = [];
        if ($phone !== '') {
            $parts[] = ($locale === 'en' ? 'Phone: ' : 'Telefon: ').$phone.'.';
        }
        if ($email !== '') {
            $parts[] = ($locale === 'en' ? 'Email: ' : 'E-posta: ').$email.'.';
        }
        $contactLine = implode(' ', $parts);

        if ($locale === 'en') {
            return "You are the friendly customer assistant for {$site} — a single real estate office in Kuşadası, Aydın. "
                 .'This is NOT a marketplace: visitors cannot register, log in, or list their own properties on the site. '
                 ."Do NOT suggest sign-up, login, account creation, or 'post a listing' — those flows do not exist here. "
                 ."\n\n"
                 .'What you SHOULD help with: '
                 .'(1) Guide the visitor to browse listings by category (satılık / kiralık daire, villa, arsa) via the İlanlar / Listings page. '
                 .'(2) Explain neighbourhoods — Türkmen, Hacıfeyzullah, Ladies Beach (Kadınlar Denizi), Güzelçamlı, Davutlar, Soğucak, Karaova. '
                 .'(3) Answer general Kuşadası real estate questions (buying process for foreigners, mortgage basics, seasonal rental income) at a high level. '
                 .'(4) For anything specific — a particular listing, a price negotiation, a viewing appointment, a document — direct them to contact the office. '
                 ."{$contactLine} "
                 ."\n\n"
                 .'Rules: Be concise (1-3 short sentences). Never invent prices, addresses, phone numbers, or personal data. '
                 ."If you don't know, say so and point to the office contact. Stay warm and professional.";
        }

        return "Sen {$site} — Kuşadası, Aydın'daki tek ofisli emlak firmasının müşteri asistanısın. "
             .'Bu site bir PAZARYERİ DEĞİL: ziyaretçiler siteye kayıt olamaz, giriş yapamaz veya kendi ilanlarını yayınlayamaz. '
             ."Kayıt olma, giriş yapma, hesap açma veya 'ilan ver' gibi yönlendirmeler YAPMA — bu akışlar burada mevcut değil. "
             ."\n\n"
             .'Yardım edebileceğin konular: '
             .'(1) Ziyaretçiyi İlanlar sayfasından kategoriye göre gezinmeye yönlendir (satılık / kiralık daire, villa, arsa). '
             .'(2) Mahalleler hakkında bilgi ver — Türkmen, Hacıfeyzullah, Kadınlar Denizi (Ladies Beach), Güzelçamlı, Davutlar, Soğucak, Karaova. '
             .'(3) Kuşadası emlak süreçleri hakkında genel bilgi ver (yabancıya satış, konut kredisi temelleri, sezonluk kira geliri). '
             .'(4) Belirli bir ilan, fiyat pazarlığı, gezme randevusu veya evrak için doğrudan ofisle iletişime yönlendir. '
             ."{$contactLine} "
             ."\n\n"
             .'Kurallar: Kısa konuş (1-3 cümle). Fiyat, adres, telefon veya kişisel veri uydurma. '
             .'Bilmediğin bir konuda doğruyu söyle ve iletişimi öner. Sıcak ve profesyonel kal.';
    }

    /* ------------------------- Provider configuration ------------------------- */

    public function activeProvider(): string
    {
        $configured = (string) get_setting('ai_provider', config('services.ai.default', self::PROVIDER_GROQ));

        return in_array($configured, [self::PROVIDER_OPENAI, self::PROVIDER_ANTHROPIC, self::PROVIDER_GROQ], true)
            ? $configured
            : self::PROVIDER_GROQ;
    }

    public function apiKey(string $provider): ?string
    {
        // Setting takes precedence so an operator can rotate keys
        // without editing .env on the server.
        $settingKey = match ($provider) {
            self::PROVIDER_ANTHROPIC => 'anthropic_api_key',
            self::PROVIDER_GROQ => 'groq_api_key',
            default => 'openai_api_key',
        };
        $fromSetting = trim((string) get_setting($settingKey, ''));
        if ($fromSetting !== '') {
            return $fromSetting;
        }

        return trim((string) config("services.ai.{$provider}.key", '')) ?: null;
    }

    public function modelName(string $provider): string
    {
        $settingKey = match ($provider) {
            self::PROVIDER_ANTHROPIC => 'anthropic_model',
            self::PROVIDER_GROQ => 'groq_model',
            default => 'openai_model',
        };
        $fromSetting = trim((string) get_setting($settingKey, ''));
        if ($fromSetting !== '') {
            return $fromSetting;
        }

        return (string) config("services.ai.{$provider}.model");
    }

    /**
     * Sağlayıcı emekliye ayırdığında yedek modeller.
     *
     * Groq zaman zaman model kimliklerini kullanımdan kaldırır (ör. Llama 3.1
     * 8B, Ağustos 2026). Ayarlardaki model artık geçerli değilse istek
     * sessizce başarısız olmasın diye sıradaki üretim modeli denenir ve
     * loglanır — operatör Ayarlar > AI'dan kalıcı olarak günceller.
     *
     * Kaynak: https://console.groq.com/docs/models (üretim modelleri)
     *
     * @var array<string, array<int, string>>
     */
    protected const FALLBACK_MODELS = [
        self::PROVIDER_GROQ => [
            'openai/gpt-oss-20b',
            'openai/gpt-oss-120b',
            'llama-3.3-70b-versatile',
        ],
    ];

    /**
     * Denenecek model listesi: önce yapılandırılan model, ardından
     * sağlayıcının bilinen yedekleri.
     *
     * @return array<int, string>
     */
    public function modelCandidates(string $provider): array
    {
        $configured = trim($this->modelName($provider));
        $candidates = array_merge(
            $configured !== '' ? [$configured] : [],
            self::FALLBACK_MODELS[$provider] ?? []
        );

        $candidates = array_values(array_unique(array_filter($candidates)));

        return $candidates !== [] ? $candidates : [$configured];
    }

    public function isConfigured(): bool
    {
        return (bool) $this->apiKey($this->activeProvider());
    }

    /* ------------------------------ Prompts ------------------------------ */

    /**
     * Build a system + user prompt pair that consistently produces
     * grounded, SEO-friendly real estate descriptions.
     */
    protected function buildListingPrompt(array $context, string $language): array
    {
        $language = strtolower($language) === 'en' ? 'en' : 'tr';

        $system = $language === 'en'
            ? 'You write concise, factual real-estate listing descriptions in English. '
            .'Use the exact data the user provides; never invent rooms, amenities, prices '
            ."or addresses that weren't supplied. Keep paragraphs short (~2-3 sentences). "
            .'End with one brief call-to-action sentence. No emojis. No promotional adjectives '
            ."like 'amazing', 'incredible', or 'unbelievable'."
            : 'Sen, Türkçe gayrimenkul ilanı açıklamaları yazan bir asistanssın. '
            .'Yalnızca kullanıcının sağladığı veriyi kullan; verilmeyen oda, fiyat, '
            .'donanım veya adres uydurma. Kısa paragraflar (2-3 cümle) yaz. '
            .'Sonda kısa bir eylem çağrısı bulunsun. Emoji kullanma. '
            ."'Muhteşem', 'inanılmaz' gibi abartılı sıfatlardan kaçın.";

        $features = $context['features'] ?? [];
        if (is_array($features)) {
            $features = array_filter(array_map('strval', $features));
        }

        $bullets = [];
        foreach ([
            'title' => $language === 'en' ? 'Title' : 'Başlık',
            'category' => $language === 'en' ? 'Category' : 'Kategori',
            'price' => $language === 'en' ? 'Price' : 'Fiyat',
            'city' => $language === 'en' ? 'City' : 'Şehir',
            'district' => $language === 'en' ? 'District' : 'İlçe',
            'neighborhood' => $language === 'en' ? 'Neighborhood' : 'Mahalle',
            'notes' => $language === 'en' ? 'Notes' : 'Notlar',
        ] as $key => $label) {
            $v = $context[$key] ?? null;
            if ($v !== null && $v !== '') {
                $bullets[] = "- {$label}: {$v}";
            }
        }
        if (! empty($features)) {
            $bullets[] = ($language === 'en' ? '- Features: ' : '- Özellikler: ').implode(', ', $features);
        }

        $instruction = $language === 'en'
            ? 'Write a 100-180 word description for the following property:'
            : 'Aşağıdaki gayrimenkul için 100-180 kelimelik bir açıklama yaz:';

        $user = $instruction."\n\n".implode("\n", $bullets);

        return [$system, $user];
    }

    /* ------------------------------ HTTP clients ------------------------------ */

    /**
     * Shared HTTP call for any OpenAI-compatible endpoint.
     * Groq and OpenAI both speak this protocol exactly.
     */
    protected function callOpenAICompatible(string $key, array $models, string $system, string $user, string $endpoint): string
    {
        return $this->postOpenAICompatible($key, $models, $endpoint, [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $user],
        ], 0.5, 600);
    }

    /**
     * Multi-turn chat over an OpenAI-compatible endpoint. Used by the
     * site chatbot widget.
     */
    protected function callOpenAICompatibleChat(string $key, array $models, string $system, array $messages, string $endpoint): string
    {
        $payload = array_merge(
            [['role' => 'system', 'content' => $system]],
            array_map(fn ($m) => ['role' => $m['role'], 'content' => $m['content']], $messages)
        );

        return $this->postOpenAICompatible($key, $models, $endpoint, $payload, 0.4, 350);
    }

    /**
     * Tek OpenAI uyumlu HTTP çağrısı + model yedekleme.
     *
     * Model kimliği geçersizse (emekliye ayrılmış / hesapta yok) sağlayıcı
     * 400/404 döner. Bu durumda sıradaki aday model denenir; diğer hatalar
     * (401 anahtar, 429 kota, 5xx) yedeklemeye girmez, doğrudan yükselir.
     *
     * @param  array<int, string>  $models
     * @param  array<int, array{role:string, content:string}>  $messages
     */
    protected function postOpenAICompatible(string $key, array $models, string $endpoint, array $messages, float $temperature, int $maxTokens): string
    {
        $host = (string) parse_url($endpoint, PHP_URL_HOST);
        $lastError = null;

        foreach (array_values($models) as $index => $model) {
            $resp = Http::withToken($key)
                ->connectTimeout(8)
                ->timeout(25)
                ->retry(2, 300, throw: false)
                ->acceptJson()
                ->post($endpoint, [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                ]);

            if ($resp->ok()) {
                $output = $this->cleanModelOutput((string) ($resp->json('choices.0.message.content') ?? ''));
                if ($output === '') {
                    throw new \RuntimeException('AI provider returned an empty response.');
                }

                if ($index > 0) {
                    Log::warning('AIService fell back to an alternate model.', [
                        'host' => $host,
                        'requested' => $models[0] ?? null,
                        'used' => $model,
                        'hint' => 'Ayarlar > AI bölümünden model adını kalıcı olarak güncelleyin.',
                    ]);
                }

                return $output;
            }

            $lastError = sprintf('%s error %d: %s', $host, $resp->status(), $resp->body());

            if (! $this->isUnknownModelError($resp->status(), (string) $resp->body())) {
                break;
            }
        }

        throw new \RuntimeException($lastError ?? 'AI provider request failed.');
    }

    /**
     * Yanıt "bu model yok / kaldırıldı" anlamına mı geliyor?
     */
    protected function isUnknownModelError(int $status, string $body): bool
    {
        if (! in_array($status, [400, 404], true)) {
            return false;
        }

        $body = strtolower($body);

        foreach (['decommission', 'does not exist', 'not found', 'model_not_found', 'unknown model', 'no longer supported'] as $needle) {
            if (str_contains($body, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function callAnthropic(string $key, string $model, string $system, string $user): string
    {
        return $this->callAnthropicChat($key, $model, $system, [
            ['role' => 'user', 'content' => $user],
        ]);
    }

    /**
     * Multi-turn chat for Anthropic. Same content-block parsing as
     * the single-turn helper.
     */
    protected function callAnthropicChat(string $key, string $model, string $system, array $messages): string
    {
        $resp = Http::withHeaders([
            'x-api-key' => $key,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])
            ->connectTimeout(8)
            ->timeout(25)
            ->retry(2, 300, throw: false)
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => 600,
                'system' => $system,
                'messages' => array_map(
                    fn ($m) => ['role' => $m['role'] === 'system' ? 'user' : $m['role'], 'content' => $m['content']],
                    $messages
                ),
            ]);

        if (! $resp->ok()) {
            throw new \RuntimeException('Anthropic error '.$resp->status().': '.$resp->body());
        }

        $blocks = $resp->json('content', []);
        $text = '';
        foreach ($blocks as $b) {
            if (($b['type'] ?? '') === 'text') {
                $text .= $b['text'] ?? '';
            }
        }
        $output = $this->cleanModelOutput($text);
        if ($output === '') {
            throw new \RuntimeException('AI provider returned an empty response.');
        }

        return $output;
    }

    /**
     * Reasoning-capable compatible models can return their private scratchpad
     * in <think> blocks. Strip it before it reaches a visitor or an editor.
     */
    protected function cleanModelOutput(string $text): string
    {
        $clean = preg_replace('/<think>.*?<\/think>\s*/is', '', $text) ?? $text;
        $clean = preg_replace('/<think>.*$/is', '', $clean) ?? $clean;
        $clean = preg_replace('/^\s*(?:analysis|reasoning)\s*:\s*/i', '', $clean) ?? $clean;
        $clean = str_replace("\0", '', $clean);

        return Str::limit(trim($clean), 8000, '');
    }
}
