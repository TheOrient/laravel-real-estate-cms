<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * On-the-fly AI translation with aggressive caching.
 *
 * The site stores most content in Turkish only. When a visitor flips
 * the locale to English, this service translates the requested text
 * via Groq (or whichever AIService provider is active) and caches the
 * result for 24 hours so the next page hit is instant and free.
 *
 * Designed to be safe to call from Blade — failures fall through to
 * the original text so the page never breaks because of a model
 * outage or a missing key.
 *
 * Usage:
 *   {{ \App\Services\AutoTranslator::auto($listing->title) }}
 *   {!! \App\Services\AutoTranslator::auto($post->body, 'html') !!}
 */
class AutoTranslator
{
    /**
     * Translate `$text` into the current request locale if it is not
     * the source locale. Returns the original text when translation
     * isn't needed, isn't possible, or fails.
     */
    public static function auto(?string $text, string $mode = 'plain', string $sourceLocale = 'tr'): string
    {
        $text = (string) $text;
        if ($text === '') {
            return '';
        }

        $target = app()->getLocale();

        // No work needed when the visitor is already on the source locale.
        if ($target === $sourceLocale) {
            return $text;
        }

        // Only TR ↔ EN is supported today. Add cases here as new
        // language packs ship.
        if (! in_array($target, ['en'], true)) {
            return $text;
        }

        return self::translate($text, $sourceLocale, $target, $mode);
    }

    /**
     * Force a translation regardless of the current locale.
     */
    public static function translate(string $text, string $from, string $to, string $mode = 'plain'): string
    {
        $text = trim($text);
        if ($text === '') {
            return $text;
        }

        // Cache by a hash of source language pair + content + mode so
        // identical inputs reuse the same answer. The version segment
        // is bumped whenever the prompt or post-process rules change,
        // forcing a fresh translation pass.
        $cacheKey = sprintf(
            'auto_translate.v6.%s.%s.%s.%s',
            $from, $to, $mode, md5($text)
        );

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($text, $from, $to, $mode) {
            try {
                /** @var AIService $ai */
                $ai = app(AIService::class);

                if (! $ai->isConfigured()) {
                    return $text;
                }

                $system = self::systemPrompt($from, $to, $mode);
                $user = $text;

                $result = $ai->chat(
                    [['role' => 'user', 'content' => $user]],
                    $system
                );

                if (is_array($result) && isset($result['error'])) {
                    return $text;
                }

                $translated = trim((string) $result);

                // A reasoning response can be truncated before </think>.
                // In that case discard the entire private scratchpad rather
                // than leaking it into a navigation label or page title.
                $translated = preg_replace('/<think>.*$/is', '', $translated) ?? $translated;

                // Reject empty answers or any output that drifted into
                // a non-Latin script (Groq occasionally returns CJK for
                // short strings); fall back to the original.
                if ($translated === '' || ! self::looksLikeLatin($translated)) {
                    return $text;
                }

                // Restore common Turkish place-name diacritics that a model
                // may strip while translating otherwise valid content.
                return self::repairProperNouns($translated);
            } catch (\Throwable $e) {
                return $text;
            }
        });
    }

    /**
     * System prompt tuned per translation mode.
     */
    protected static function systemPrompt(string $from, string $to, string $mode): string
    {
        $fromName = $from === 'tr' ? 'Turkish' : strtoupper($from);
        $toName = $to === 'en' ? 'English' : strtoupper($to);

        // Hard guardrails on the OUTPUT script — some models drop to
        // Chinese or Arabic for short / ambiguous strings. The phrase
        // "Latin alphabet only" plus a final reminder shape behaviour
        // reliably for Groq's Llama models.
        if ($mode === 'html') {
            return "You translate real-estate website content from {$fromName} to {$toName}. "
                ."OUTPUT MUST BE WRITTEN IN {$toName} USING THE LATIN ALPHABET ONLY. "
                .'Preserve every HTML tag and attribute exactly — start and close every '
                .'tag the source has. Translate ONLY the visible text and text inside '
                .'title/alt attributes. Do not add explanations, do not wrap in code fences. '
                .'Never output reasoning or <think> tags. '
                .'PROPER NAMES — keep EXACTLY as written: '
                .'Türkmen, Güzelçamlı, Hacıfeyzullah, Davutlar, Karaova, Soğucak, Kuşadası, '
                .'Aydın. Preserve numbers, '
                .'prices, currency symbols and addresses as-is.';
        }

        return "You translate real-estate website content from {$fromName} to {$toName}. "
            ."OUTPUT MUST BE WRITTEN IN {$toName} USING THE LATIN ALPHABET ONLY. "
            .'Never use Chinese, Arabic, Cyrillic or any non-Latin characters. '
            .'Output ONLY the translated text — no quotes, no preface, no commentary, no reasoning or <think> tags. '
            .'PROPER NAMES — keep these EXACTLY as written with no translation: '
            .'Türkmen, Güzelçamlı, Hacıfeyzullah, '
            .'Davutlar, Karaova, Soğucak, Güvercinada, Kuşadası, Aydın, Antalya, '
            .'İzmir, İstanbul, Konyaaltı, Manavgat, Alanya, Side, Beylikdüzü, '
            .'Caddebostan, Kadıköy, Nilüfer. Preserve numbers, prices, currency symbols '
            .'and addresses as-is. Be concise and natural.';
    }

    /**
     * Cheap sanity check used by the cache layer — discards any
     * translation that came back with CJK / Cyrillic / Arabic glyphs.
     */
    public static function looksLikeLatin(string $text): bool
    {
        // CJK Unified Ideographs / Hiragana / Katakana / Hangul / Arabic / Cyrillic.
        return ! preg_match('/[\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}\p{Arabic}\p{Cyrillic}]/u', $text);
    }

    /**
     * Deterministic post-process repair pass for proper nouns the LLM
     * tends to mistranslate or strip diacritics from.
     *
     * Keys are case-insensitive regex patterns; values are the canonical
     * Turkish forms. Word boundaries avoid corrupting unrelated words.
     */
    protected static function repairProperNouns(string $text): string
    {
        $repairs = [
            // Kuşadası neighbourhoods — Latin-only LLM output strips
            // Turkish diacritics. Reinstate the canonical spelling.
            '/\bKusadasi\b/iu' => 'Kuşadası',
            '/\bTurkmen Mahallesi\b/iu' => 'Türkmen Mahallesi',
            '/\bTurkmen\/Hacifeyzullah\b/iu' => 'Türkmen / Hacıfeyzullah',
            '/\bTurkmen \/ Hacifeyzullah\b/iu' => 'Türkmen / Hacıfeyzullah',
            '/\bGuzelcamli\b/iu' => 'Güzelçamlı',
            '/\bHacifeyzullah\b/iu' => 'Hacıfeyzullah',
            '/\bSogucak\b/iu' => 'Soğucak',
            '/\bGuvercinada\b/iu' => 'Güvercinada',
            '/\bAydin\b/iu' => 'Aydın',
        ];

        foreach ($repairs as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }

        return $text;
    }
}
