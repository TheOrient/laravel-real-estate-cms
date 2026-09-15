@component('emails.layouts.mail', [
    'title' => __('mail.listing_expired_subject'),
    'heading' => __('mail.listing_expired_heading'),
    'icon' => '⏰',
    'headerType' => 'red'
])

<p>{{ __('mail.greeting') }} <strong>{{ $user->full_name }}</strong>,</p>

<div class="info-box error">
    <p><strong>⚠️ {{ __('mail.important_note') }}</strong></p>
    <p>{{ __('mail.listing_expired_intro') }}</p>
</div>

<div class="info-box">
    <h3>{{ $listing->title }}</h3>
    <div class="field-group">
        <div class="field"><strong>{{ __('mail.category') }}:</strong> {{ $listing->category->name }}</div>
        <div class="field"><strong>{{ __('mail.first_published') }}:</strong> {{ $listing->published_at->format('d.m.Y') }}</div>
        <div class="field"><strong>{{ __('mail.expiration_date') }}:</strong> {{ $listing->expires_at->format('d.m.Y') }}</div>
    </div>
</div>

<div class="info-box warning">
    <p><strong>ℹ️ {{ __('mail.republish_info') }}</strong></p>
    <p>{{ __('mail.republish_action') }}</p>
</div>

<div class="button-group">
    <a href="{{ route('user.listings.edit', $listing->id) }}" class="button primary">{{ __('mail.edit_listing') }}</a>
    <a href="{{ route('user.listings.my') }}" class="button secondary">{{ __('mail.my_listings') }}</a>
</div>

@endcomponent
