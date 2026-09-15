{{--
    User Panel Navigation Component

    Usage: @include('user.partials.navigation', ['active' => 'dashboard'])

    Available active options:
    - dashboard
    - profile
    - create
    - listings
--}}

<!-- User Panel Navigation -->
<div class="bg-white rounded-xl shadow-sm border mb-8 overflow-hidden">
    <div class="flex overflow-x-auto">
        <!-- Dashboard -->
        <a href="{{ route('user.dashboard') }}"
           class="px-6 py-4 text-sm font-medium whitespace-nowrap transition-colors {{ $active === 'dashboard' ? 'border-b-2 border-[#1E6F5C] text-[#1E6F5C]' : 'text-gray-500 hover:text-[#1E6F5C] border-b-2 border-transparent hover:border-gray-300' }}">
            <i class="ri-dashboard-line mr-2"></i>{{ __('user.dashboard') }}
        </a>

        <!-- Profile -->
        <a href="{{ route('user.profile') }}"
           class="px-6 py-4 text-sm font-medium whitespace-nowrap transition-colors {{ $active === 'profile' ? 'border-b-2 border-[#1E6F5C] text-[#1E6F5C]' : 'text-gray-500 hover:text-[#1E6F5C] border-b-2 border-transparent hover:border-gray-300' }}">
            <i class="ri-user-line mr-2"></i>{{ __('user.profile') }}
        </a>

        <!-- Create Listing -->
        <a href="{{ route('user.listings.create') }}"
           class="px-6 py-4 text-sm font-medium whitespace-nowrap transition-colors {{ $active === 'create' ? 'border-b-2 border-[#1E6F5C] text-[#1E6F5C]' : 'text-gray-500 hover:text-[#1E6F5C] border-b-2 border-transparent hover:border-gray-300' }}">
            <i class="ri-add-circle-line mr-2"></i>{{ __('user.create_portfolio_listing') }}
        </a>

        <!-- My Listings -->
        <a href="{{ route('user.listings.my') }}"
           class="px-6 py-4 text-sm font-medium whitespace-nowrap transition-colors {{ $active === 'listings' ? 'border-b-2 border-[#1E6F5C] text-[#1E6F5C]' : 'text-gray-500 hover:text-[#1E6F5C] border-b-2 border-transparent hover:border-gray-300' }}">
            <i class="ri-list-check mr-2"></i>{{ __('user.my_portfolio') }}
        </a>
    </div>
</div>
