@component('emails.layouts.mail', [
    'title' => __('mail.contact_message_received_subject'),
    'heading' => __('mail.contact_message_received_heading'),
    'icon' => '✉️',
    'headerType' => 'success',
    'subheading' => config('app.name')
])

<p>{{ __('mail.greeting') }},</p>
<p>{{ __('mail.contact_message_received_intro') }}</p>

<div class="info-box">
    <div class="field-group">
        <div class="field"><strong>{{ __('mail.full_name') }}:</strong> {{ $contactMessage->name }}</div>
        <div class="field"><strong>{{ __('mail.email') }}:</strong> {{ $contactMessage->email }}</div>
        @if($contactMessage->phone)
        <div class="field"><strong>{{ __('mail.phone') }}:</strong> {{ $contactMessage->phone }}</div>
        @endif
        <div class="field"><strong>{{ __('mail.subject') }}:</strong> {{ $contactMessage->subject }}</div>
        <div class="field"><strong>{{ __('mail.send_date') }}:</strong> {{ $contactMessage->created_at->format('d.m.Y H:i') }}</div>
        <div class="field"><strong>{{ __('mail.ip_address') }}:</strong> {{ $contactMessage->ip_address }}</div>
    </div>
</div>

<div class="message-box">
    <strong>{{ __('mail.message') }}:</strong>
    <p>{!! nl2br(e($contactMessage->message)) !!}</p>
</div>

<div class="button-group">
    <a href="{{ url('/admin/contact-messages/' . $contactMessage->id) }}" class="button success">
        {{ __('mail.view_in_admin') }}
    </a>
</div>

@slot('additionalFooter')
    <p>{{ __('mail.reply_to_sender') }}</p>
    <p><strong>{{ __('mail.sender') }} {{ __('mail.email') }}:</strong> {{ $contactMessage->email }}</p>
@endslot

@endcomponent
