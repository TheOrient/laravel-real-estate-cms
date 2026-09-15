<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use App\Services\ChatbotFallbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public chat endpoint for the floating site assistant.
 *
 * - Anonymous (no auth required) so visitors can use it.
 * - Throttled via `throttle:chatbot` (registered in routes).
 * - Conversation history is sent by the client every turn; the server
 *   is stateless. This keeps deployment simple and removes any
 *   session-storage tie-in.
 */
class ChatbotController extends Controller
{
    public function __construct(
        protected AIService $ai,
        protected ChatbotFallbackService $fallback,
    ) {}

    public function reply(Request $request): JsonResponse
    {
        // Enabled by default; only an explicit '0' disables. This makes
        // the feature work on fresh installs without the operator
        // having to toggle anything in admin settings.
        if ((string) get_setting('chatbot_enabled', '1') === '0') {
            return response()->json([
                'ok'    => false,
                'error' => __('chatbot.disabled'),
            ], 403);
        }

        $data = $request->validate([
            'messages'                 => ['required', 'array', 'min:1', 'max:20'],
            'messages.*.role'          => ['required', 'in:user,assistant'],
            'messages.*.content'       => ['required', 'string', 'max:2000'],
        ]);

        $lastMessage = (string) collect($data['messages'])->last()['content'];

        if (! $this->ai->isConfigured()) {
            return response()->json([
                'ok'    => true,
                'reply' => $this->fallback->reply($lastMessage),
                'mode'  => 'local',
            ]);
        }

        $reply = $this->ai->chat($data['messages']);

        if (is_array($reply) && isset($reply['error'])) {
            return response()->json([
                'ok'    => true,
                'reply' => $this->fallback->reply($lastMessage),
                'mode'  => 'local',
            ]);
        }

        return response()->json([
            'ok'    => true,
            'reply' => (string) $reply,
            'mode'  => 'ai',
        ]);
    }
}
