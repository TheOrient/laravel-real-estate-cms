<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Deterministic safety net for the public assistant.
 *
 * The widget stays useful before an AI key is configured and during a
 * temporary provider outage. It only answers from approved office topics.
 */
class ChatbotFallbackService
{
    public function reply(string $message): string
    {
        $message = Str::lower(Str::ascii($message));

        if (Str::contains($message, ['ilan ara', 'ilanlar', 'portfoy', 'satilik', 'kiralik', 'villa', 'daire', 'listing', 'portfolio'])) {
            return __('chatbot.fallback_search');
        }

        if (Str::contains($message, ['bolge', 'mahalle', 'kusadasi', 'turkmen', 'kadinlar', 'davutlar', 'guzelcamli', 'sogucak', 'karaova', 'area', 'neighbourhood', 'neighborhood'])) {
            return __('chatbot.fallback_areas');
        }

        if (Str::contains($message, ['mulkumu', 'evimi', 'satmak', 'kiralamak', 'ilan ver', 'property', 'sell', 'rent out', 'list my'])) {
            return __('chatbot.fallback_owner');
        }

        if (Str::contains($message, ['iletisim', 'telefon', 'email', 'e-posta', 'randevu', 'contact', 'phone', 'appointment', 'viewing'])) {
            return __('chatbot.fallback_contact');
        }

        return __('chatbot.fallback_default');
    }
}
