@component('emails.layouts.mail', [
    'title' => __('mail.listing_submitted_subject'),
    'heading' => __('mail.listing_submitted_heading'),
    'icon' => '📝',
    'headerType' => 'primary'
])

<p>{{ __('mail.greeting') }} <strong>{{ $listing->user->name }}</strong>,</p>
<p>{{ __('mail.listing_submitted_intro') }}</p>

<div class="info-box">
    <h3>{{ $listing->title }}</h3>
    <div class="field-group">
        <div class="field"><strong>{{ __('mail.category') }}:</strong> {{ $listing->category->name }}</div>
        <div class="field"><strong>{{ __('mail.price') }}:</strong> {{ number_format($listing->price, 2) }} ₺</div>
        <div class="field"><strong>{{ __('mail.submission_date') }}:</strong> {{ $listing->created_at->format('d.m.Y H:i') }}</div>
    </div>
</div>

<div class="info-box info">
    <p><strong>{{ __('mail.what_happens_next') }}</strong></p>
    <ul>
        <li>{{ __('mail.admin_review_process') }}</li>
        <li>{{ __('mail.notification_when_approved') }}</li>
        <li>{{ __('mail.listing_will_be_published') }}</li>
    </ul>
</div>

<p>{{ __('mail.thanks_for_submission') }}</p>

<div class="button-group">
    <a href="{{ route('user.listings.my') }}" class="button primary">{{ __('mail.my_listings') }}</a>
</div>

@endcomponent
