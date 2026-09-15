@php
    // Enabled by default; only an explicit '0' from the settings table
    // disables the widget. Mirrors the controller logic.
    $chatEnabled = (string) get_setting('chatbot_enabled', '1') !== '0';
@endphp

@if($chatEnabled)
{{-- Position + z-index pinned inline. Bottom offset is large enough
     that the debugbar (when APP_DEBUG=true) doesn't cover the trigger
     button. --}}
<div id="chatbot-root"
     style="position: fixed; bottom: 4.5rem; right: 1.5rem; z-index: 9999; font-family: Inter, sans-serif;">

    {{-- Trigger button (visible when panel is closed). Symmetric
         padding + centered inline layout so the icon and label read
         as one balanced pill instead of drifting right. --}}
    <button type="button" id="chatbot-trigger"
            class="group text-white bg-[#1E6F5C] hover:bg-[#155946] rounded-full shadow-2xl transition-all hover:-translate-y-0.5"
            style="display: inline-flex; align-items: center; justify-content: center; gap: 0.6rem; padding: 0.6rem 1.15rem 0.6rem 0.65rem; line-height: 1;"
            aria-label="{{ __('chatbot.open_chat') }}" aria-expanded="false" aria-controls="chatbot-panel">
        <span class="relative" style="display: inline-flex; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border-radius: 50%; background: rgba(255,255,255,0.16); flex-shrink: 0;">
            <i class="ri-customer-service-2-line" style="font-size: 1.15rem; line-height: 1;"></i>
            <span class="animate-pulse" style="position: absolute; top: -2px; right: -2px; width: 0.7rem; height: 0.7rem; background: #6ee7b7; border-radius: 50%; border: 2px solid #1E6F5C;"></span>
        </span>
        <span class="hidden sm:inline" style="font-size: 0.85rem; font-weight: 700; letter-spacing: 0.01em; line-height: 1;">{{ __('chatbot.assistant_title') }}</span>
    </button>

    {{-- Panel — dimensions pinned inline so missing Tailwind JIT
         classes can't blow the layout up. --}}
    <div id="chatbot-panel"
         class="hidden flex-col bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
         style="width: 360px; max-width: calc(100vw - 2.5rem); height: 560px; max-height: calc(100vh - 7rem);">
        {{-- Header --}}
        <div class="flex items-center justify-between p-4 bg-gradient-to-br from-[#1E6F5C] to-[#13493E] text-white">
            <div class="flex items-center gap-3">
                <span class="relative flex h-10 w-10 items-center justify-center rounded-full bg-white/15">
                    <i class="ri-customer-service-2-line text-lg"></i>
                    <span class="absolute -bottom-0 -right-0 w-3 h-3 bg-emerald-300 rounded-full border-2 border-[#13493E]"></span>
                </span>
                <div>
                    <div class="text-sm font-bold leading-tight">{{ __('chatbot.assistant_title') }}</div>
                    <div class="text-[11px] text-white/70 leading-tight">{{ __('chatbot.assistant_status') }}</div>
                </div>
            </div>
            <button type="button" id="chatbot-close" class="w-8 h-8 inline-flex items-center justify-center rounded-full hover:bg-white/10"
                    aria-label="{{ __('chatbot.close_chat') }}">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>

        {{-- Message list --}}
        <div id="chatbot-messages" role="log" aria-live="polite" aria-label="{{ __('chatbot.assistant_title') }}" style="flex: 1 1 auto; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: 0.875rem; background: #F5F7F8;">
            {{-- Greeting bubble --}}
            <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
                <div style="flex-shrink: 0; width: 1.75rem; height: 1.75rem; border-radius: 50%; background: #1E6F5C; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                    <i class="ri-customer-service-2-line"></i>
                </div>
                <div style="max-width: 80%; background: #fff; border-radius: 1rem 1rem 1rem 0.25rem; padding: 0.75rem 1rem; font-size: 0.875rem; line-height: 1.55; color: #1A1A1A; box-shadow: 0 1px 2px rgba(0,0,0,0.06); word-wrap: break-word; text-align: left;">
                    {{ __('chatbot.greeting') }}
                </div>
            </div>

            {{-- Quick suggestion chips --}}
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; padding-top: 0.25rem;" id="chatbot-quick">
                @foreach(['quick_1', 'quick_2', 'quick_3'] as $key)
                    <button type="button"
                            class="chatbot-quick-chip"
                            style="font-size: 0.75rem; padding: 0.375rem 0.75rem; border-radius: 999px; background: #fff; border: 1px solid #E5E7EB; color: #1E6F5C; cursor: pointer; transition: background-color .2s, color .2s;">
                        {{ __('chatbot.' . $key) }}
                    </button>
                @endforeach
            </div>

            <div class="chatbot-shortcuts" style="display: flex; flex-wrap: wrap; gap: 0.75rem; padding-left: 2.25rem;">
                <a href="{{ route('categories.show.all') }}" style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem; font-weight: 700; color: #1E6F5C; text-decoration: none;">
                    <i class="ri-home-search-line"></i>{{ __('chatbot.browse_listings') }}
                </a>
                <a href="{{ route('pages.show', 'iletisim') }}" style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem; font-weight: 700; color: #1E6F5C; text-decoration: none;">
                    <i class="ri-phone-line"></i>{{ __('chatbot.contact_office') }}
                </a>
            </div>
        </div>

        {{-- Input --}}
        <form id="chatbot-form" class="border-t border-gray-100 bg-white p-3 flex items-end gap-2">
            <textarea id="chatbot-input" rows="1" maxlength="1000"
                      class="flex-1 resize-none max-h-32 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E6F5C]/30 focus:border-[#1E6F5C]"
                      placeholder="{{ __('chatbot.placeholder') }}"></textarea>
            <button type="submit" id="chatbot-send" aria-label="{{ __('chatbot.send') }}"
                    class="w-10 h-10 flex items-center justify-center bg-[#1E6F5C] hover:bg-[#155946] text-white rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="ri-send-plane-2-fill text-lg"></i>
            </button>
        </form>
        <div class="text-[10px] text-center text-gray-400 pb-2">{{ __('chatbot.powered_by') }}</div>
    </div>
</div>

@push('styles')
<style>
    @keyframes chatbot-bounce {
        0%, 80%, 100% { transform: scale(0.8); opacity: 0.6; }
        40% { transform: scale(1.2); opacity: 1; }
    }
    #chatbot-panel { transform-origin: bottom right; }
    #chatbot-messages > div { width: 100%; box-sizing: border-box; }
    #chatbot-messages a:focus-visible,
    #chatbot-root button:focus-visible,
    #chatbot-root textarea:focus-visible { outline: 3px solid rgba(30,111,92,.28); outline-offset: 2px; }
    @media (max-width: 520px) {
        #chatbot-root { right: 0.75rem !important; bottom: 1rem !important; }
        #chatbot-panel { width: calc(100vw - 1.5rem) !important; height: min(620px, calc(100vh - 2rem)) !important; max-height: calc(100vh - 2rem) !important; }
        #chatbot-trigger { padding-right: 0.75rem !important; }
    }
</style>
@endpush
@push('scripts')
<script>
(function () {
    var root = document.getElementById('chatbot-root');
    if (!root) return;

    var trigger = document.getElementById('chatbot-trigger');
    var panel   = document.getElementById('chatbot-panel');
    var closer  = document.getElementById('chatbot-close');
    var form    = document.getElementById('chatbot-form');
    var input   = document.getElementById('chatbot-input');
    var sendBtn = document.getElementById('chatbot-send');
    var msgList = document.getElementById('chatbot-messages');
    var quickRow= document.getElementById('chatbot-quick');

    // Conversation history sent to the backend on every turn. Server is
    // stateless — we keep the transcript here.
    var history = [];

    function openPanel() {
        panel.classList.remove('hidden');
        panel.classList.add('flex');
        trigger.classList.add('hidden');
        trigger.style.display = 'none';
        trigger.setAttribute('aria-expanded', 'true');
        setTimeout(function () { input.focus(); }, 100);
    }
    function closePanel() {
        panel.classList.add('hidden');
        panel.classList.remove('flex');
        trigger.classList.remove('hidden');
        trigger.style.display = 'inline-flex';
        trigger.setAttribute('aria-expanded', 'false');
    }
    trigger.addEventListener('click', openPanel);
    closer.addEventListener('click', closePanel);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.classList.contains('hidden')) closePanel();
    });

    // Submit on Enter, newline on Shift+Enter.
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.requestSubmit();
        }
    });
    input.addEventListener('input', function () {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 128) + 'px';
    });

    quickRow && quickRow.addEventListener('click', function (e) {
        var btn = e.target.closest('.chatbot-quick-chip');
        if (!btn) return;
        input.value = btn.textContent.trim();
        form.requestSubmit();
    });

    function appendBubble(role, text) {
        var wrap = document.createElement('div');
        // Use inline styles so the bubble shape works even when
        // Tailwind arbitrary utilities aren't in the compiled CSS.
        wrap.style.cssText = 'display:flex; align-items:flex-start; gap:0.5rem;'
            + (role === 'user' ? ' justify-content:flex-end;' : '');

        if (role === 'user') {
            wrap.innerHTML = '<div style="max-width:80%; background:#1E6F5C; color:#fff; border-radius:1rem 1rem 0.25rem 1rem; padding:0.75rem 1rem; font-size:0.875rem; line-height:1.55; box-shadow:0 1px 2px rgba(0,0,0,0.06); word-wrap:break-word; white-space:pre-wrap; text-align:left;"></div>';
        } else {
            wrap.innerHTML =
                '<div style="flex-shrink:0; width:1.75rem; height:1.75rem; border-radius:50%; background:#1E6F5C; color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.75rem;"><i class="ri-customer-service-2-line"></i></div>' +
                '<div style="max-width:80%; background:#fff; color:#1A1A1A; border-radius:1rem 1rem 1rem 0.25rem; padding:0.75rem 1rem; font-size:0.875rem; line-height:1.55; box-shadow:0 1px 2px rgba(0,0,0,0.06); word-wrap:break-word; white-space:pre-wrap; text-align:left;"></div>';
        }
        var bubble = wrap.querySelector('div:last-child');
        bubble.textContent = text;
        msgList.appendChild(wrap);
        msgList.scrollTop = msgList.scrollHeight;
        return bubble;
    }

    function showThinking() {
        var wrap = document.createElement('div');
        wrap.style.cssText = 'display:flex; align-items:flex-start; gap:0.5rem;';
        wrap.id = 'chatbot-thinking';
        wrap.innerHTML =
            '<div style="flex-shrink:0; width:1.75rem; height:1.75rem; border-radius:50%; background:#1E6F5C; color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.75rem;"><i class="ri-customer-service-2-line"></i></div>' +
            '<div style="background:#fff; border-radius:1rem 1rem 1rem 0.25rem; padding:0.75rem 1rem; box-shadow:0 1px 2px rgba(0,0,0,0.06);">' +
                '<span style="display:flex; gap:0.25rem;">' +
                    '<span style="width:0.375rem; height:0.375rem; background:#9CA3AF; border-radius:50%; animation: chatbot-bounce 1.4s infinite ease-in-out; animation-delay:0ms;"></span>' +
                    '<span style="width:0.375rem; height:0.375rem; background:#9CA3AF; border-radius:50%; animation: chatbot-bounce 1.4s infinite ease-in-out; animation-delay:150ms;"></span>' +
                    '<span style="width:0.375rem; height:0.375rem; background:#9CA3AF; border-radius:50%; animation: chatbot-bounce 1.4s infinite ease-in-out; animation-delay:300ms;"></span>' +
                '</span>' +
            '</div>';
        msgList.appendChild(wrap);
        msgList.scrollTop = msgList.scrollHeight;
    }
    function hideThinking() {
        var t = document.getElementById('chatbot-thinking');
        if (t) t.remove();
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = (input.value || '').trim();
        if (!text) return;

        if (quickRow) { quickRow.style.display = 'none'; }

        appendBubble('user', text);
        history.push({ role: 'user', content: text });
        input.value = '';
        input.style.height = 'auto';
        sendBtn.disabled = true;
        showThinking();

        var csrf = document.querySelector('meta[name="csrf-token"]');
        fetch(@json(route('chatbot.reply')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf ? csrf.content : '',
            },
            body: JSON.stringify({ messages: history }),
        })
        .then(function (r) { return r.json().then(function (d) { return { status: r.status, data: d }; }); })
        .then(function (res) {
            hideThinking();
            sendBtn.disabled = false;
            if (res.data && res.data.ok && res.data.reply) {
                appendBubble('assistant', res.data.reply);
                history.push({ role: 'assistant', content: res.data.reply });
            } else {
                appendBubble('assistant', (res.data && res.data.error) || @json(__('chatbot.error_generic')));
            }
        })
        .catch(function () {
            hideThinking();
            sendBtn.disabled = false;
            appendBubble('assistant', @json(__('chatbot.error_generic')));
        });
    });
})();
</script>
@endpush
@endif
