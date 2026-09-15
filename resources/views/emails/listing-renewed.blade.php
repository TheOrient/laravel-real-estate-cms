@component('emails.layouts.mail', [
    'title' => __('mail.listing_renewed_subject'),
    'heading' => __('mail.listing_renewed_heading'),
    'icon' => '🎉',
    'headerType' => 'green'
])

<p>{{ __('mail.greeting') }} <strong>{{ $user->full_name }}</strong>,</p>

<div class="info-box success">
    <p><strong>✅ Harika haber!</strong></p>
    <p>{{ __('mail.listing_renewed_intro') }}</p>
</div>

<div class="info-box">
    <h3>{{ $listing->title }}</h3>
    <div class="field-group">
        <div class="field"><strong>{{ __('mail.category') }}:</strong> {{ $listing->category->name }}</div>
        <div class="field"><strong>{{ __('mail.first_published') }}:</strong> {{ $listing->published_at->format('d.m.Y') }}</div>
        <div class="field"><strong>{{ __('mail.new_expiration_date') }}:</strong> <span style="color: #10b981; font-weight: bold;">{{ $listing->expires_at->format('d.m.Y') }}</span></div>
    </div>
</div>

<div class="info-box info">
    <p><strong>ℹ️ {{ __('mail.renewal_info') }}:</strong></p>
    <p>{{ __('mail.renewal_action') }}</p>
</div>

<div class="button-group">
    <a href="{{ route('listings.show', $listing->slug) }}" class="button success">{{ __('mail.view_listing') }}</a>
    <a href="{{ route('user.listings.my') }}" class="button secondary">{{ __('mail.my_listings') }}</a>
</div>

@endcomponent
