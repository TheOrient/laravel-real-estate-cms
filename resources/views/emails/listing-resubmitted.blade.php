@component('emails.layouts.mail', [
    'title' => __('mail.listing_resubmitted_subject'),
    'heading' => __('mail.listing_resubmitted_heading'),
    'icon' => '🔄',
    'headerType' => 'warning'
])

<p>{{ __('mail.greeting') }} <strong>{{ $listing->user->name }}</strong>,</p>
<p>{{ __('mail.listing_resubmitted_intro') }}</p>

<div class="info-box">
    <h3>{{ $listing->title }}</h3>
    <div class="field-group">
        <div class="field"><strong>{{ __('mail.category') }}:</strong> {{ $listing->category->name }}</div>
        <div class="field"><strong>{{ __('mail.price') }}:</strong> {{ number_format($listing->price, 2) }} ₺</div>
        <div class="field"><strong>{{ __('mail.update_date') }}:</strong> {{ $listing->updated_at->format('d.m.Y H:i') }}</div>
    </div>
</div>

<div class="info-box warning">
    <p><strong>{{ __('mail.important_note') }}</strong></p>
    <p>{{ __('mail.resubmission_note') }}</p>
</div>

<p>{{ __('mail.thanks_for_update') }}</p>

<div class="button-group">
    <a href="{{ route('user.listings.my') }}" class="button warning">{{ __('mail.my_listings') }}</a>
</div>

@endcomponent
