@component('emails.layouts.mail', [
    'title' => __('mail.listing_approved_subject'),
    'heading' => __('mail.listing_approved_heading'),
    'icon' => '✓',
    'headerType' => 'success'
])

<p>{{ __('mail.greeting') }} <strong>{{ $listing->user->name }}</strong>,</p>
<p>{{ __('mail.listing_approved_intro') }}</p>

<div class="info-box">
    <h3>{{ $listing->title }}</h3>
    <div class="field-group">
        <div class="field"><strong>{{ __('mail.category') }}:</strong> {{ $listing->category->name }}</div>
        <div class="field"><strong>{{ __('mail.approval_date') }}:</strong> {{ now()->format('d.m.Y H:i') }}</div>
    </div>
</div>

<p>{{ __('mail.listing_visible') }}</p>

<div class="button-group">
    <a href="{{ route('listings.show', $listing->slug) }}" class="button success">{{ __('mail.view_listing') }}</a>
    <a href="{{ route('user.listings.my') }}" class="button secondary">{{ __('mail.my_listings') }}</a>
</div>

@endcomponent
