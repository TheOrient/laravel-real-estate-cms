@component('emails.layouts.mail', [
    'title' => __('mail.new_listing_admin_subject'),
    'heading' => __('mail.new_listing_admin_heading'),
    'icon' => '🔔',
    'headerType' => 'info'
])

<p>{{ __('mail.greeting') }} Admin,</p>
<p>{{ __('mail.new_listing_admin_intro') }}</p>

<div class="info-box">
    <h3>{{ $listing->title }}</h3>
    <div class="field-group">
        <div class="field"><strong>{{ __('mail.user') }}:</strong> {{ $listing->user->name }} ({{ $listing->user->email }})</div>
        <div class="field"><strong>{{ __('mail.category') }}:</strong> {{ $listing->category->name }}</div>
        <div class="field"><strong>{{ __('mail.date') }}:</strong> {{ $listing->created_at->format('d.m.Y H:i') }}</div>
        @if($listing->price)
        <div class="field"><strong>{{ __('mail.price') }}:</strong> {{ number_format($listing->price, 2) }} TL</div>
        @endif
    </div>
</div>

<p>Lütfen ilanı inceleyin ve uygunsa onaylayın.</p>

<div class="button-group">
    <a href="{{ route('admin.listings.show', $listing->id) }}" class="button info">{{ __('mail.review_listing') }}</a>
    <a href="{{ route('admin.listings.index', ['approval_status' => 'pending']) }}" class="button secondary">{{ __('mail.pending_listings') }}</a>
</div>

@endcomponent
