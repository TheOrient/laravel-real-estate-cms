@component('emails.layouts.mail', [
    'title' => __('mail.listing_rejected_subject'),
    'heading' => __('mail.listing_rejected_heading'),
    'icon' => '⚠',
    'headerType' => 'error'
])

<p>{{ __('mail.greeting') }} <strong>{{ $listing->user->name }}</strong>,</p>
<p>{{ __('mail.listing_rejected_intro') }}</p>

<div class="info-box">
    <h3>{{ $listing->title }}</h3>
    <div class="field-group">
        <div class="field"><strong>{{ __('mail.category') }}:</strong> {{ $listing->category->name }}</div>
    </div>
</div>

@if($reason)
<div class="info-box error">
    <strong>{{ __('mail.rejection_reason') }}:</strong>
    <p>{{ $reason }}</p>
</div>
@endif

<p>{{ __('mail.listing_rejected_action') }}</p>

<div class="button-group">
    <a href="{{ route('user.listings.edit', $listing->id) }}" class="button success">{{ __('mail.edit_listing') }}</a>
    <a href="{{ route('user.listings.my') }}" class="button secondary">{{ __('mail.my_listings') }}</a>
</div>

@endcomponent
