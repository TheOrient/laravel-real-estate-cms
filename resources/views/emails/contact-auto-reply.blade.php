@component('emails.layouts.mail', [
    'title' => __('mail.contact_auto_reply_subject'),
    'heading' => __('mail.contact_auto_reply_heading'),
    'icon' => '✓',
    'headerType' => 'success'
])

<p>{{ __('mail.greeting') }} <strong>{{ $contactMessage->name }}</strong>,</p>

<div class="info-box success">
    <p><strong>✓ {{ __('mail.message_sent_success') }}</strong></p>
    <p>{{ __('mail.contact_auto_reply_intro') }}</p>
</div>

<div class="message-box">
    <h3>{{ __('mail.your_message') }}:</h3>
    <div class="field-group">
        <div class="field"><strong>{{ __('mail.subject') }}:</strong> {{ $contactMessage->subject }}</div>
    </div>
    <p><strong>{{ __('mail.message') }}:</strong></p>
    <p style="white-space: pre-wrap;">{{ $contactMessage->message }}</p>
</div>

<p><strong>{{ __('mail.message_details') }}:</strong></p>
<ul>
    <li><strong>{{ __('mail.send_date') }}:</strong> {{ $contactMessage->created_at->format('d.m.Y H:i') }}</li>
    <li><strong>{{ __('mail.reference_no') }}:</strong> #{{ $contactMessage->id }}</li>
</ul>

<div class="info-box">
    <h3>{{ __('mail.contact_info') }}:</h3>
    <p>{{ __('mail.urgent_contact') }}</p>
    <ul>
        <li><strong>{{ __('mail.email') }}:</strong> {{ config('mail.from.address') }}</li>
        <li><strong>{{ __('mail.website') }}:</strong> {{ config('app.url') }}</li>
    </ul>
</div>

<p>{{ __('mail.response_time') }}</p>

@slot('additionalFooter')
    <p>{{ __('mail.not_your_message') }}</p>
@endslot

@endcomponent
