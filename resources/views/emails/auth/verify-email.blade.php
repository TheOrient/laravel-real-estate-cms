@component('emails.layouts.mail', [
    'title' => __('mail.verify_email_subject'),
    'heading' => __('mail.verify_email_heading'),
    'icon' => '✉️',
    'headerType' => 'success'
])

<p>{{ __('mail.greeting') }} <strong>{{ $user->name }}</strong>,</p>

<p>{{ __('mail.verify_email_intro') }}</p>

<div class="info-box success">
    <p><strong>✓ {{ __('mail.verify_email_welcome') }}</strong></p>
    <p>{{ __('mail.verify_email_welcome_message') }}</p>
</div>

<p>{{ __('mail.verify_email_action') }}</p>

<div class="button-group">
    <a href="{{ $url }}" class="button success">{{ __('mail.verify_email_button') }}</a>
</div>

<p>{{ __('mail.verify_email_expires') }}</p>

<div class="info-box info">
    <p><strong>{{ __('mail.link_not_working') }}</strong></p>
    <p>{{ __('mail.copy_paste_link') }}</p>
    <p style="word-break: break-all; font-size: 12px; color: #6b7280;">{{ $url }}</p>
</div>

<p>{{ __('mail.verify_email_footer') }}</p>

@endcomponent
