<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Public maps use OpenStreetMap/Leaflet by default and require no key.
    // A different provider can be selected later through environment config.
    'map' => [
        'provider' => env('MAPS_PROVIDER', env('MAP_PROVIDER', 'openstreetmap')),
        // CARTO basemap anahtarı (ücretsiz, kredi kartı istemez, ticari
        // kullanıma açıktır — aylık 5M döşeme isteği).
        // Alınacağı yer: https://carto.com  →  ücretsiz hesap → API Keys
        // Boş bırakılırsa harita yine çalışır; anahtar eklemek CARTO'nun
        // kullanım koşullarına tam uyum sağlar ve hız sınırı riskini kaldırır.
        'tile_key' => env('MAP_TILE_API_KEY', ''),
        'center' => [
            'lat' => 37.8579,
            'lng' => 27.2610,
        ],
        'zoom' => [
            'default' => 11,
            'city' => 13,
            'listing' => 16,
        ],
    ],

    // Optional Google Maps configuration for a future provider switch.
    'google' => [
        'maps' => [
            'api_key' => env('GOOGLE_MAPS_API_KEY'), // Ücretsiz: boş bırakılabilir
        ],
    ],

    // Shortcut alias used by the <x-map-display /> / <x-map-picker /> blade
    // components. Keeping it as its own entry avoids a deep config()
    // path inside every view.
    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI providers (Task #9)
    |--------------------------------------------------------------------------
    | Both OpenAI and Anthropic are supported. The active provider and its
    | model are picked from the settings table at request time (see
    | AIService). Keys can live in either .env or the settings table —
    | settings table wins when set, so the operator can rotate keys
    | without touching the .env file.
    */
    'ai' => [
        // Default provider when no setting is present. Groq is the
        // most accessible free option (no card required).
        'default' => env('AI_PROVIDER', 'groq'),

        'openai' => [
            'key'   => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        ],
        'anthropic' => [
            'key'   => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-latest'),
        ],
        'groq' => [
            'key'   => env('GROQ_API_KEY'),
            // The previous Llama 3.3 model was retired for developer-tier
            // accounts in August 2026. This current production model is
            // available to the configured Groq project.
            'model' => env('GROQ_MODEL', 'openai/gpt-oss-20b'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Graph API (Task #10 — auto-post)
    |--------------------------------------------------------------------------
    */
    'meta' => [
        'graph_version'  => env('META_GRAPH_VERSION', 'v19.0'),
        'page_id'        => env('META_PAGE_ID'),
        'page_token'     => env('META_PAGE_TOKEN'),
        'ig_user_id'     => env('META_IG_USER_ID'),
    ],

];
