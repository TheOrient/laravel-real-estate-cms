@extends('layouts.app')

@section('title', __('listings.create_title') . ' - ' . __('listings.step_2'))

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('listings.create_title') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('listings.step_2') }}</p>
            <p class="mt-1 text-xs text-blue-600">{{ __('listings.category_label') }}: {{ $category->name }}</p>
        </div>

        <!-- Progress Steps -->
        <x-listing-create-progress :current-step="2" />
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto mt-10">
    <form action="{{ route('user.listings.create.step2.store') }}" method="POST" id="essentialsForm">
        @csrf

        <div class="bg-white rounded-2xl shadow-lg p-8">
            {{-- Tek dil (varsayılan) — başlık ve açıklama --}}
            @php
                $lid = $defaultLanguage->id;
                $descOld = old("descriptions.$lid", data_get(session('listing_essentials'), "descriptions.$lid", []));
                $descOld = is_array($descOld) ? $descOld : [];
            @endphp
            <div class="mb-8">
                <h3 class="text-lg font-bold text-[#1A1A1A] mb-6" style="font-family: Inter, sans-serif;">{{ __('listings.listing_info') }}</h3>
                <div class="space-y-6">
                    <div>
                        <label for="title_{{ $lid }}" class="block text-sm font-semibold text-[#1A1A1A] mb-2">{{ __('components/language-tabs.listing_title') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="descriptions[{{ $lid }}][title]" id="title_{{ $lid }}" required maxlength="255"
                               value="{{ $descOld['title'] ?? '' }}"
                               class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] @error('descriptions.'.$lid.'.title') border-red-500 @enderror">
                        @error('descriptions.'.$lid.'.title')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="description_{{ $lid }}" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('components/language-tabs.listing_description') }} <span class="text-red-500">*</span></label>
                            <button type="button"
                                    id="ai-help-{{ $lid }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-[#1E6F5C] bg-[#1E6F5C]/10 hover:bg-[#1E6F5C] hover:text-white transition-colors">
                                <i class="ri-magic-line"></i>
                                <span>{{ __('listings.ai_help') }}</span>
                            </button>
                        </div>
                        <textarea name="descriptions[{{ $lid }}][description]" id="description_{{ $lid }}" rows="6" required
                                  class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] @error('descriptions.'.$lid.'.description') border-red-500 @enderror">{{ $descOld['description'] ?? '' }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="ri-information-line"></i>
                            {{ __('listings.ai_help_hint') }}
                        </p>
                        @error('descriptions.'.$lid.'.description')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Price Section --}}
            <div class="mb-8">
                <h3 class="text-lg font-bold text-[#1A1A1A] mb-4" style="font-family: Inter, sans-serif;">{{ __('listings.price_section') }}</h3>
                <div>
                    <label for="price" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('listings.price_label') }}</label>
                    <div class="relative">
                        <input type="number" name="price" id="price" value="{{ old('price', session('listing_essentials.price')) }}"
                               class="w-full px-4 py-3 pr-12 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all @error('price') border-red-500 @enderror"
                               placeholder="{{ __('listings.price_placeholder') }}" min="0" step="0.01" required style="font-family: Inter, sans-serif;">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <span class="text-[#666666] text-sm" style="font-family: Inter, sans-serif;">{{ __('listings.currency_symbol') }}</span>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-[#666666]" style="font-family: Inter, sans-serif;">{{ __('listings.price_help') }}</p>
                    @error('price')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Location Section -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-[#1A1A1A] mb-4" style="font-family: Inter, sans-serif;">{{ __('listings.location_section') }}</h3>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- City -->
                    <div>
                        <label for="city_id" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('listings.city_label') }}</label>
                        <select name="city_id" id="city_id"
                                class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all cursor-pointer @error('city_id') border-red-500 @enderror" required style="font-family: Inter, sans-serif;">
                            <option value="">{{ __('listings.city_placeholder') }}</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id', session('listing_essentials.city_id')) == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('city_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- District -->
                    <div>
                        <label for="district_id" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('listings.district_label') }}</label>
                        <select name="district_id" id="district_id"
                                class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all cursor-pointer @error('district_id') border-red-500 @enderror" required disabled style="font-family: Inter, sans-serif;">
                            <option value="">{{ __('listings.district_select_first') }}</option>
                        </select>
                        @error('district_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Neighborhood -->
                <div class="mt-6">
                    <label for="neighborhood_id" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('listings.neighborhood_label') }}</label>
                    <select name="neighborhood_id" id="neighborhood_id"
                            class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all cursor-pointer @error('neighborhood_id') border-red-500 @enderror" disabled style="font-family: Inter, sans-serif;">
                        <option value="">{{ __('listings.neighborhood_select_first') }}</option>
                    </select>
                    @error('neighborhood_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Map picker — optional fine-grained coordinates that
                 land on the listing detail page as an interactive map.
                 Falls back to OpenStreetMap when no Google Maps key. --}}
            <div class="mt-10">
                <h3 class="text-lg font-bold text-[#1A1A1A] mb-2 flex items-center" style="font-family: Inter, sans-serif;">
                    <i class="ri-map-pin-2-line mr-2 text-[#1E6F5C]"></i>
                    {{ __('listings.location_on_map') }}
                    <span class="ml-2 text-xs font-normal text-gray-400">({{ __('listings.optional') }})</span>
                </h3>
                <x-map-picker
                    name="coordinates"
                    :latitude="old('coordinates.latitude', data_get(session('listing_essentials'), 'coordinates.latitude'))"
                    :longitude="old('coordinates.longitude', data_get(session('listing_essentials'), 'coordinates.longitude'))" />
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-6 flex justify-between">
            <a href="{{ route('user.listings.create') }}" class="inline-flex items-center px-6 py-3 border border-[#E0E0E0] text-sm font-bold rounded-xl text-[#1A1A1A] bg-white hover:bg-[#F5F7F8] transition-all duration-300 whitespace-nowrap cursor-pointer" style="font-family: Inter, sans-serif; font-weight: 700;">
                <i class="ri-arrow-left-line mr-2"></i>
                {{ __('listings.back') }}
            </a>

            <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl text-white bg-[#1E6F5C] hover:bg-[#13493E] transition-all duration-300 whitespace-nowrap cursor-pointer" style="font-family: Inter, sans-serif; font-weight: 700;">
                {{ __('listings.next_step') }}
                <i class="ri-arrow-right-line ml-2"></i>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Global variables for pre-selected values
const preSelectedDistrict = '{{ old("district_id", session("listing_essentials.district_id")) }}';
const preSelectedNeighborhood = '{{ old("neighborhood_id", session("listing_essentials.neighborhood_id")) }}';

// Location cascading dropdowns
document.getElementById('city_id').addEventListener('change', function() {
    const cityId = this.value;
    const districtSelect = document.getElementById('district_id');
    const neighborhoodSelect = document.getElementById('neighborhood_id');

    // Reset district and neighborhood
    districtSelect.innerHTML = '<option value="">{{ __('listings.district_placeholder') }}</option>';
    neighborhoodSelect.innerHTML = '<option value="">{{ __('listings.neighborhood_select_first') }}</option>';
    districtSelect.disabled = !cityId;
    neighborhoodSelect.disabled = true;

    if (cityId) {
        console.log('Loading districts for city:', cityId);
        // Fetch districts
        fetch(`/api/cities/${cityId}/districts`)
            .then(response => {
                console.log('Districts API response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(districts => {
                console.log('Districts loaded:', districts.length);
                districtSelect.disabled = false;

                districts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.id;
                    option.textContent = district.name;

                    // Check if this district should be pre-selected
                    if (preSelectedDistrict && preSelectedDistrict == district.id) {
                        option.selected = true;
                        console.log('Pre-selected district:', district.name);
                    }

                    districtSelect.appendChild(option);
                });

                // Trigger district change if there's a pre-selected district
                if (districtSelect.value) {
                    console.log('Triggering district change for:', districtSelect.value);
                    districtSelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => {
                console.error('Error loading districts:', error);
                alert('{{ __('listings.error_loading_districts') }}' + error.message);
            });
    }
});

document.getElementById('district_id').addEventListener('change', function() {
    const districtId = this.value;
    const neighborhoodSelect = document.getElementById('neighborhood_id');

    // Reset neighborhood
    neighborhoodSelect.innerHTML = '<option value="">{{ __('listings.neighborhood_placeholder') }}</option>';
    neighborhoodSelect.disabled = !districtId;

    if (districtId) {
        console.log('Loading neighborhoods for district:', districtId);
        // Fetch neighborhoods
        fetch(`/api/districts/${districtId}/neighborhoods`)
            .then(response => {
                console.log('Neighborhoods API response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(neighborhoods => {
                console.log('Neighborhoods loaded:', neighborhoods.length);
                neighborhoodSelect.disabled = false;

                neighborhoods.forEach(neighborhood => {
                    const option = document.createElement('option');
                    option.value = neighborhood.id;
                    option.textContent = neighborhood.name;

                    // Check if this neighborhood should be pre-selected
                    if (preSelectedNeighborhood && preSelectedNeighborhood == neighborhood.id) {
                        option.selected = true;
                        console.log('Pre-selected neighborhood:', neighborhood.name);
                    }

                    neighborhoodSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading neighborhoods:', error);
                alert('{{ __('listings.error_loading_neighborhoods') }}' + error.message);
            });
    }
});

// Trigger city change on page load if there's old input or session data
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, checking for pre-selected city');
    const citySelect = document.getElementById('city_id');
    if (citySelect.value) {
        console.log('Found pre-selected city:', citySelect.value);
        // Add a small delay to ensure all elements are ready
        setTimeout(() => {
            citySelect.dispatchEvent(new Event('change'));
        }, 100);
    }
});

// Form validation
document.getElementById('essentialsForm').addEventListener('submit', function(e) {
    const price = document.getElementById('price').value;
    const cityId = document.getElementById('city_id').value;
    const districtId = document.getElementById('district_id').value;

    if (!price || !cityId || !districtId) {
        e.preventDefault();
        alert('{{ __('listings.validation_error') }}');
        return false;
    }

    if (parseFloat(price) < 0) {
        e.preventDefault();
        alert('{{ __('listings.price_validation') }}');
        return false;
    }
});

// ===== AI assist button =====
// Delegated handler so it works regardless of how many language tabs
// exist on this page.
document.addEventListener('click', function (e) {
    var btn = e.target.closest('button[id^="ai-help-"]');
    if (!btn) return;
    e.preventDefault();

    // Match the language id from the button (ai-help-{lid}).
    var lid = btn.id.replace('ai-help-', '');
    var titleEl = document.getElementById('title_' + lid);
    var descEl  = document.getElementById('description_' + lid);
    if (!titleEl || !descEl) return;

    var title = (titleEl.value || '').trim();
    if (!title) {
        alert(@json(__('listings.ai_title_required')));
        titleEl.focus();
        return;
    }

    // Best-effort: pull selected city/district label so AI gets context.
    var citySel = document.getElementById('city_id');
    var distSel = document.getElementById('district_id');
    var priceEl = document.getElementById('price');
    var city = citySel ? (citySel.options[citySel.selectedIndex]?.text || '').trim() : '';
    var district = distSel ? (distSel.options[distSel.selectedIndex]?.text || '').trim() : '';
    var price = priceEl ? priceEl.value : '';

    var original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i> ' + @json(__('listings.ai_generating'));

    fetch(@json(route('user.ai.generate-listing-description')), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            title: title,
            category: @json(isset($category) ? $category->name : null),
            price: price,
            city: city && city !== '—' && city.indexOf('seç') === -1 ? city : '',
            district: district && district !== '—' && district.indexOf('seç') === -1 ? district : '',
            notes: descEl.value, // existing text as hint
            language: @json(app()->getLocale()),
        }),
    })
    .then(function (r) { return r.json().then(function (d) { return { status: r.status, data: d }; }); })
    .then(function (res) {
        if (res.data && res.data.ok && res.data.text) {
            descEl.value = res.data.text;
            descEl.dispatchEvent(new Event('input', { bubbles: true }));
        } else {
            alert((res.data && res.data.error) || @json(__('listings.ai_error')));
        }
    })
    .catch(function (err) { alert(@json(__('listings.ai_error')) + ' ' + err.message); })
    .finally(function () { btn.disabled = false; btn.innerHTML = original; });
});
</script>
@endpush
@endsection
