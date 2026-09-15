@extends('layouts.app')

@section('title', __('user.dashboard'))

@section('content')
<div class="container mx-auto px-4 max-w-[1200px] py-8 mt-6">
    @include('user.partials.navigation', ['active' => 'dashboard'])
    <!-- Welcome Header with Time-based Greeting -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
            @php
                $hour = now()->hour;
                if ($hour < 12) {
                    $greeting = __('user.good_morning');
                } elseif ($hour < 17) {
                    $greeting = __('user.good_afternoon');
                } else {
                    $greeting = __('user.good_evening');
                }
            @endphp
            {{ $greeting }}, {{ $user->full_name }}! 👋
        </h1>
        <p class="mt-2 text-lg text-gray-600">{{ __('user.agent_panel_summary') }}</p>
    </div>

    <!-- Enhanced Statistics Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-8">
        <!-- Total Listings -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 overflow-hidden shadow-sm rounded-xl border border-blue-200 hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="ri-list-check text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-blue-700 truncate">{{ __('user.total_listings') }}</dt>
                        <dd class="text-2xl font-bold text-blue-900">{{ $stats['total_listings'] }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Listings -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 overflow-hidden shadow-sm rounded-xl border border-green-200 hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="ri-check-circle-line text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-green-700 truncate">{{ __('user.active_listings') }}</dt>
                        <dd class="text-2xl font-bold text-green-900">{{ $stats['active_listings'] }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Draft Listings -->
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden shadow-sm rounded-xl border border-gray-200 hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gray-500 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="ri-edit-line text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-700 truncate">{{ __('user.draft') }}</dt>
                        <dd class="text-2xl font-bold text-gray-900">{{ $stats['draft_listings'] }}</dd>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Quick Actions -->
    <div class="mb-8">
        <h3 class="text-xl font-semibold text-gray-900 mb-6">🚀 {{ __('user.quick_actions') }}</h3>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Create New Listing -->
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border hover:shadow-md transition-all hover:-translate-y-1 duration-200">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" style="background:linear-gradient(135deg,#1E6F5C,#13493E);">
                                <i class="ri-add-line text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900">{{ __('user.create_portfolio_listing') }}</h4>
                            <p class="text-sm text-gray-500">{{ __('user.create_listing_desc') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('user.listings.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 w-full justify-center" style="background:#1E6F5C;">
                        {{ __('user.create_portfolio_listing') }}
                        <i class="ri-arrow-right-line ml-2"></i>
                    </a>
                </div>
            </div>

            <!-- My Listings -->
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border hover:shadow-md transition-all hover:-translate-y-1 duration-200">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="ri-list-check text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900">{{ __('user.my_portfolio') }}</h4>
                            <p class="text-sm text-gray-500">{{ __('user.view_manage_listings') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('user.listings.my') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 w-full justify-center" style="background:#2B7A67;">
                        {{ __('user.view_portfolio') }}
                        <i class="ri-arrow-right-line ml-2"></i>
                    </a>
                </div>
            </div>

            <!-- Profile -->
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border hover:shadow-md transition-all hover:-translate-y-1 duration-200">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="ri-user-line text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900">{{ __('user.profile') }}</h4>
                            <p class="text-sm text-gray-500">{{ __('user.update_profile_info') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('user.profile') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 w-full justify-center" style="background:#365E54;">
                        {{ __('user.edit_profile') }}
                        <i class="ri-arrow-right-line ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Recent Activity -->
    <div>
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">📋 {{ __('user.recent_listings') }}</h3>
            @if($user->listings()->count() > 5)
                <a href="{{ route('user.listings.my') }}" class="text-[#1E6F5C] hover:text-[#13493E] text-sm font-medium">
                    {{ __('user.view_all') }} →
                </a>
            @endif
        </div>
        <div class="bg-white shadow-sm overflow-hidden rounded-xl border">
            @if($user->listings()->latest()->limit(5)->count() > 0)
                <ul class="divide-y divide-gray-100">
                    @foreach($user->listings()->with('category')->latest()->limit(5)->get() as $listing)
                        <li class="hover:bg-gray-50 transition-colors">
                            <div class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center min-w-0 flex-1">
                                        <div class="flex-shrink-0">
                                            @if($listing->is_active && $listing->is_approved)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 shadow-sm">
                                                    <i class="ri-check-line mr-1"></i>
                                                    {{ __('user.listing_live') }}
                                                </span>
                                            @elseif(!$listing->is_active)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 shadow-sm">
                                                    <i class="ri-pause-line mr-1"></i>
                                                    {{ __('listings.inactive') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 shadow-sm">
                                                    <i class="ri-eye-off-line mr-1"></i>
                                                    {{ __('user.listing_not_public') }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="ml-4 min-w-0 flex-1">
                                            <p class="text-base font-medium text-gray-900 truncate">{{ $listing->title }}</p>
                                            <p class="text-sm text-gray-500">
                                                <span class="inline-flex items-center">
                                                    <i class="ri-folder-line mr-1"></i>
                                                    {{ $listing->category->name }}
                                                </span>
                                                <span class="mx-2">•</span>
                                                <span class="inline-flex items-center">
                                                    <i class="ri-time-line mr-1"></i>
                                                    {{ $listing->created_at->diffForHumans() }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <span class="text-lg font-semibold text-primary">{{ number_format($listing->price, 0) }} ₺</span>
                                        <a href="{{ route('user.listings.edit', $listing) }}" class="text-gray-400 hover:text-primary transition-colors">
                                            <i class="ri-edit-line text-lg"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="ri-folder-open-line text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('user.no_listings_yet') }}</h3>
                    <p class="text-gray-500 mb-6">{{ __('user.create_first_listing_desc') }}</p>
                    <a href="{{ route('user.listings.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200" style="background:#1E6F5C;">
                        <i class="ri-add-line mr-2"></i>
                        {{ __('user.create_first_listing') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
/* Enhanced animations and interactions */
.hover\:scale-105:hover {
    transform: scale(1.05);
}

.hover\:-translate-y-1:hover {
    transform: translateY(-4px);
}

/* Smooth gradient animations */
.bg-gradient-to-r {
    background-size: 200% 200%;
    animation: gradient 3s ease infinite;
}

@keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Enhanced shadow effects */
.shadow-lg {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>
@endpush
@endsection
