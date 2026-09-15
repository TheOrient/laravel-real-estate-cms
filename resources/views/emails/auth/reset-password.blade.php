@component('emails.layouts.mail', [
    'title' => __('mail.reset_password_subject'),
    'heading' => __('mail.reset_password_heading'),
    'icon' => '🔑',
    'headerType' => 'primary'
])

<p>{{ __('mail.greeting') }} <strong>{{ $user->name }}</strong>,</p>

<p>{{ __('mail.reset_password_intro') }}</p>

<div class="info-box warning">
    <p><strong>⚠️ {{ __('mail.important_note') }}</strong></p>
    <p>{{ __('mail.reset_password_note') }}</p>
</div>

<div class="button-group">
    <a href="{{ $url }}" class="button primary">{{ __('mail.reset_password_button') }}</a>
</div>

<p>{{ __('mail.reset_password_expires', ['count' => $count]) }}</p>

<div class="info-box info">
    <p><strong>{{ __('mail.link_not_working') }}</strong></p>
    <p>{{ __('mail.copy_paste_link') }}</p>
    <p style="word-break: break-all; font-size: 12px; color: #6b7280;">{{ $url }}</p>
</div>

<p>{{ __('mail.reset_password_footer') }}</p>

@endcomponent
