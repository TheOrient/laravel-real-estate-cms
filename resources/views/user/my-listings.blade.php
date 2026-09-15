@extends('layouts.app')

@section('title', __('user.my_listings'))

@section('content')
<div class="container mx-auto px-4 max-w-[1200px] py-8">
    @include('user.partials.navigation', ['active' => 'listings'])

    <!-- Enhanced Header with Action -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('user.my_listings_title') }}</h1>
            <p class="text-sm text-[#666666]" style="font-family: Inter, sans-serif;">{{ __('user.my_listings_description') }}</p>
        </div>
        <a href="{{ route('user.listings.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl text-white bg-[#1E6F5C] hover:bg-[#13493E] transition-all duration-300 whitespace-nowrap cursor-pointer" style="font-family: Inter, sans-serif; font-weight: 700;">
            <i class="ri-add-line mr-2"></i>
            {{ __('user.create_listing') }}
        </a>
    </div>

    <div class="space-y-8">
        @if($listings->count() > 0)
            <!-- Enhanced Stats Cards -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-3 mb-8">
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-[#1E6F5C]/10 rounded-xl flex items-center justify-center">
                            <i class="ri-list-check text-[#1E6F5C] text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-[#1A1A1A]" style="font-family: Inter, sans-serif;">{{ $listings->total() }}</div>
                            <div class="text-sm text-[#666666]" style="font-family: Inter, sans-serif;">{{ __('user.total_listings') }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <i class="ri-check-circle-line text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-[#1A1A1A]" style="font-family: Inter, sans-serif;">{{ $listings->where('is_active', true)->where('is_approved', true)->count() }}</div>
                            <div class="text-sm text-[#666666]" style="font-family: Inter, sans-serif;">{{ __('user.listing_live') }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                            <i class="ri-pause-circle-line text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-[#1A1A1A]" style="font-family: Inter, sans-serif;">{{ $listings->where('is_active', false)->count() }}</div>
                            <div class="text-sm text-[#666666]" style="font-family: Inter, sans-serif;">{{ __('listings.inactive') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Listings Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($listings as $listing)
                    <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 cursor-pointer">
                        <!-- Enhanced Image Container -->
                        <div class="relative">
                            @if($listing->images->count() > 0)
                                <img loading="lazy" decoding="async" src="{{ listing_image_url($listing->images->first()->image, 'medium', 'crop') }}"
                                     alt="{{ $listing->title }}"
                                     class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                    <div class="text-center">
                                        <i class="ri-image-line text-gray-400 text-4xl mb-2"></i>
                                        <p class="text-gray-500 text-sm">{{ __('listings.no_photo') }}</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Price Badge -->
                            <div class="absolute top-4 left-4 bg-[#1E6F5C] text-white px-4 py-2 rounded-xl font-bold text-sm" style="font-family: Inter, sans-serif;">
                                {{ number_format($listing->price, 0) }} ₺
                            </div>

                            <!-- Category Badge -->
                            <div class="absolute top-4 right-4 bg-white/90 text-[#1A1A1A] px-3 py-1 rounded-lg text-xs font-semibold" style="font-family: Inter, sans-serif;">
                                {{ $listing->category->name }}
                            </div>

                            <!-- Kullanıcı paneli: onay kuyruğu yok; yayında / pasif / yayında değil -->
                            <div class="absolute bottom-4 left-4 flex gap-2">
                                @if($listing->is_active && $listing->is_approved)
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-green-500 text-white">
                                        <i class="ri-check-line mr-1"></i>
                                        {{ __('user.listing_live') }}
                                    </span>
                                @elseif(!$listing->is_active)
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-gray-600 text-white">
                                        <i class="ri-pause-line mr-1"></i>
                                        {{ __('listings.inactive') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-gray-500 text-white">
                                        <i class="ri-eye-off-line mr-1"></i>
                                        {{ __('user.listing_not_public') }}
                                    </span>
                                @endif
                            </div>

                            <!-- View Count Overlay -->
                            <div class="absolute bottom-3 right-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-black bg-opacity-70 text-white">
                                    <i class="ri-eye-line mr-1"></i>
                                    {{ $listing->view_count }}
                                </span>
                            </div>
                        </div>

                        <!-- Enhanced Content -->
                        <div class="p-5">
                            <h4 class="text-lg font-bold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif; font-weight: 700;">
                                {{ $listing->title }}
                            </h4>
                            <p class="text-sm text-[#1A1A1A]/60 mb-4 flex items-center" style="font-family: Inter, sans-serif; font-weight: 400;">
                                <i class="ri-map-pin-line mr-1"></i>{{ $listing->city->name }}, {{ $listing->district->name }}
                            </p>
                            <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                                @if($listing->area)
                                <div class="flex items-center text-sm text-[#1A1A1A]/70">
                                    <i class="ri-ruler-line mr-1"></i>
                                    <span style="font-family: Inter, sans-serif;">{{ $listing->area }} m²</span>
                                </div>
                                @endif
                                @if($listing->rooms)
                                <div class="flex items-center text-sm text-[#1A1A1A]/70">
                                    <i class="ri-door-open-line mr-1"></i>
                                    <span style="font-family: Inter, sans-serif;">{{ $listing->rooms }} {{ __('listings.room') }}</span>
                                </div>
                                @endif
                                @if($listing->floor)
                                <div class="flex items-center text-sm text-[#1A1A1A]/70">
                                    <i class="ri-building-line mr-1"></i>
                                    <span style="font-family: Inter, sans-serif;">{{ $listing->floor }}. {{ __('listings.floor') }}</span>
                                </div>
                                @endif
                            </div>
                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                <!-- Edit -->
                                <a href="{{ route('user.listings.edit', $listing) }}"
                                   class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-[#E0E0E0] text-xs font-semibold rounded-xl text-[#1A1A1A] bg-white hover:bg-[#F5F7F8] transition-all duration-300" style="font-family: Inter, sans-serif;">
                                    <i class="ri-edit-line mr-1"></i>
                                    {{ __('user.edit') }}
                                </a>

                                <!-- View -->
                                @if($listing->is_active && $listing->is_approved)
                                    <a href="{{ route('listings.show', $listing->slug) }}"
                                       target="_blank"
                                       class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-[#1E6F5C] text-xs font-semibold rounded-xl text-[#1E6F5C] bg-[#1E6F5C]/10 hover:bg-[#1E6F5C]/20 transition-all duration-300" style="font-family: Inter, sans-serif;">
                                        <i class="ri-external-link-line mr-1"></i>
                                        {{ __('user.view_all') }}
                                    </a>
                                @endif

                                <!-- Delete -->
                                <button onclick="deleteListing({{ $listing->id }}, '{{ $listing->title }}')"
                                        class="inline-flex items-center justify-center px-3 py-2 border border-transparent text-xs font-semibold rounded-xl text-red-700 bg-red-100 hover:bg-red-200 transition-all duration-300" style="font-family: Inter, sans-serif;">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>

                            {{-- Durum yönetimi: yayına al / yayından kaldır,
                                 aktif-pasif ve öne çıkarma. Hepsi POST + CSRF. --}}
                            <div class="flex items-center gap-2 mt-2">
                                @if($listing->is_approved)
                                    <form method="POST" action="{{ route('user.listings.unpublish', $listing) }}" class="flex-1">
                                        @csrf
                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center px-3 py-2 border border-[#E0E0E0] text-xs font-semibold rounded-xl text-[#1A1A1A] bg-white hover:bg-[#F5F7F8] transition-all duration-300" style="font-family: Inter, sans-serif;">
                                            <i class="ri-eye-off-line mr-1"></i>{{ __('user.unpublish_action') }}
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('user.listings.publish', $listing) }}" class="flex-1">
                                        @csrf
                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center px-3 py-2 border border-transparent text-xs font-semibold rounded-xl text-white bg-[#1E6F5C] hover:bg-[#13493E] transition-all duration-300" style="font-family: Inter, sans-serif;">
                                            <i class="ri-upload-cloud-line mr-1"></i>{{ __('user.publish_action') }}
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('user.listings.toggle-active', $listing) }}" class="flex-1">
                                    @csrf
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center px-3 py-2 border border-[#E0E0E0] text-xs font-semibold rounded-xl text-[#1A1A1A] bg-white hover:bg-[#F5F7F8] transition-all duration-300" style="font-family: Inter, sans-serif;">
                                        <i class="{{ $listing->is_active ? 'ri-pause-line' : 'ri-play-line' }} mr-1"></i>
                                        {{ $listing->is_active ? __('user.deactivate_action') : __('user.activate_action') }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('user.listings.toggle-featured', $listing) }}">
                                    @csrf
                                    <button type="submit"
                                            title="{{ $listing->is_featured ? __('user.unfeature_action') : __('user.feature_action') }}"
                                            class="inline-flex items-center justify-center px-3 py-2 border text-xs font-semibold rounded-xl transition-all duration-300 {{ $listing->is_featured ? 'border-transparent text-amber-800 bg-amber-100 hover:bg-amber-200' : 'border-[#E0E0E0] text-[#1A1A1A] bg-white hover:bg-[#F5F7F8]' }}" style="font-family: Inter, sans-serif;">
                                        <i class="{{ $listing->is_featured ? 'ri-star-fill' : 'ri-star-line' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Enhanced Pagination -->
            <div class="bg-white px-6 py-4 flex items-center justify-between border border-gray-200 rounded-xl shadow-sm">
                <div class="flex-1 flex justify-between sm:hidden">
                    @if ($listings->onFirstPage())
                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-gray-100 border border-gray-300 cursor-default leading-5 rounded-lg">
                            {{ __('listings.previous') }}
                        </span>
                    @else
                        <a href="{{ $listings->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-lg hover:text-gray-500 focus:outline-none focus:ring ring-primary focus:border-primary active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                            {{ __('listings.previous') }}
                        </a>
                    @endif

                    @if ($listings->hasMorePages())
                        <a href="{{ $listings->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-lg hover:text-gray-500 focus:outline-none focus:ring ring-primary focus:border-primary active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                            {{ __('listings.next') }}
                        </a>
                    @else
                        <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-gray-100 border border-gray-300 cursor-default leading-5 rounded-lg">
                            {{ __('listings.next') }}
                        </span>
                    @endif
                </div>

                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 leading-5 flex items-center">
                            <i class="ri-file-list-3-line mr-2 text-primary"></i>
                            <span class="font-medium">{{ $listings->firstItem() }}</span>
                            {{ __('listings.showing_range') }}
                            <span class="font-medium">{{ $listings->lastItem() }}</span>
                            {{ __('listings.showing_range') }}
                            <span class="font-medium">{{ $listings->total() }}</span>
                            {{ __('listings.total_results') }}
                        </p>
                    </div>

                    <div>
                        <nav class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px" aria-label="Pagination">
                            {{-- Previous Page Link --}}
                            @if ($listings->onFirstPage())
                                <span aria-disabled="true" aria-label="{{ __('listings.previous') }}">
                                    <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-300 cursor-default rounded-l-lg leading-5" aria-hidden="true">
                                        <i class="ri-arrow-left-s-line"></i>
                                    </span>
                                </span>
                            @else
                                <a href="{{ $listings->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-lg leading-5 hover:text-primary hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring ring-primary focus:border-primary active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="{{ __('listings.previous') }}">
                                    <i class="ri-arrow-left-s-line"></i>
                                </a>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($listings->getUrlRange(1, $listings->lastPage()) as $page => $url)
                                @if ($page == $listings->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-semibold text-white bg-primary border border-primary cursor-default leading-5">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-primary hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring ring-primary focus:border-primary active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" aria-label="{{ __('listings.page') }} {{ $page }}">{{ $page }}</a>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($listings->hasMorePages())
                                <a href="{{ $listings->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-3 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-lg leading-5 hover:text-primary hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring ring-primary focus:border-primary active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="{{ __('listings.next') }}">
                                    <i class="ri-arrow-right-s-line"></i>
                                </a>
                            @else
                                <span aria-disabled="true" aria-label="{{ __('listings.next') }}">
                                    <span class="relative inline-flex items-center px-3 py-2 -ml-px text-sm font-medium text-gray-300 bg-gray-100 border border-gray-300 cursor-default rounded-r-lg leading-5" aria-hidden="true">
                                        <i class="ri-arrow-right-s-line"></i>
                                    </span>
                                </span>
                            @endif
                        </nav>
                    </div>
                </div>
            </div>
        @else
            <!-- Enhanced Empty State -->
            <div class="text-center py-16">
                <div class="mx-auto flex items-center justify-center h-32 w-32 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 mb-6">
                    <i class="ri-folder-open-line text-gray-400 text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ __('user.no_listings') }}</h3>
                <p class="text-lg text-gray-500 mb-8 max-w-md mx-auto">
                    {{ __('user.no_listings_description') }}
                </p>
                <div class="space-y-4">
                    <a href="{{ route('user.listings.create') }}"
                       class="inline-flex items-center px-8 py-4 border border-transparent text-base font-bold rounded-xl text-white bg-[#1E6F5C] hover:bg-[#13493E] transition-all duration-300 whitespace-nowrap cursor-pointer" style="font-family: Inter, sans-serif; font-weight: 700;">
                        <i class="ri-add-line mr-2"></i>
                        {{ __('user.create_first_listing') }}
                    </a>
                    <p class="text-sm text-[#666666]" style="font-family: Inter, sans-serif;">{{ __('listings.free_and_easy') }}</p>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Enhanced Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-xl rounded-xl bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                <i class="ri-delete-bin-line text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('listings.delete_listing') }}</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500 mb-4">
                    <strong id="listingTitle"></strong> {{ __('listings.delete_confirmation', ['title' => '']) }}
                </p>
                <p class="text-xs text-red-600">
                    {{ __('listings.action_irreversible') }}
                </p>
            </div>
            <div class="flex items-center justify-center gap-3 px-4 py-3">
                <button id="cancelDelete" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    <i class="ri-close-line mr-2"></i>
                    {{ __('listings.cancel') }}
                </button>
                <button id="confirmDelete" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                    <i class="ri-delete-bin-line mr-2"></i>
                    {{ __('listings.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let listingToDelete = null;

function deleteListing(id, title) {
    listingToDelete = id;
    document.getElementById('listingTitle').textContent = title;
    document.getElementById('deleteModal').classList.remove('hidden');
}

document.getElementById('cancelDelete').addEventListener('click', function() {
    document.getElementById('deleteModal').classList.add('hidden');
    listingToDelete = null;
});

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (listingToDelete) {
        // Show loading state
        this.disabled = true;
        this.innerHTML = '<i class="ri-loader-4-line mr-2 animate-spin"></i>{{ __('listings.deleting') }}';

        // Create and submit form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/panel/listing') }}/${listingToDelete}`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';

        form.appendChild(csrfToken);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
});

// Close modal on backdrop click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
        listingToDelete = null;
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('deleteModal').classList.add('hidden');
        listingToDelete = null;
    }
});
</script>
@endpush

@push('styles')
<style>
/* Enhanced card hover effects */
.hover\:-translate-y-1:hover {
    transform: translateY(-4px);
}

/* Smooth image transitions */
img {
    transition: transform 0.3s ease;
}

.hover\:scale-105:hover img {
    transform: scale(1.05);
}

/* Line clamp utilities */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Enhanced modal backdrop */
#deleteModal {
    backdrop-filter: blur(4px);
}

/* Improved animations */
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

#deleteModal > div {
    animation: fadeIn 0.3s ease-out;
}

/* Enhanced button transitions */
button, a {
    transition: all 0.2s ease;
}

/* Loading spinner */
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endpush
@endsection
