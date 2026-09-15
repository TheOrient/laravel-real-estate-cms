@extends('layouts.app')

@section('title', __('listings.edit_title'))

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('listings.edit_title') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $listing->title }}</p>
        </div>
        <a href="{{ route('user.listings.my') }}" class="inline-flex items-center px-6 py-3 border border-[#E0E0E0] text-sm font-bold rounded-xl text-[#1A1A1A] bg-white hover:bg-[#F5F7F8] transition-all duration-300 whitespace-nowrap cursor-pointer" style="font-family: Inter, sans-serif; font-weight: 700;">
            <i class="ri-arrow-left-line mr-2"></i>
            {{ __('listings.my_listings') }}
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto mt-10">
    <form action="{{ route('user.listings.update', $listing) }}" method="POST" id="editListingForm">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <!-- Category Section -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-[#1A1A1A] mb-4" style="font-family: Inter, sans-serif;">{{ __('listings.category_label') }}</h3>
                <div>
                    <label for="category_id" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('listings.category_label') }}</label>
                    <select name="category_id" id="category_id"
                            class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all cursor-pointer @error('category_id') border-red-500 @enderror" required style="font-family: Inter, sans-serif;">
                        <option value="">{{ __('listings.city_placeholder') }}</option>
                        <x-category-three :categories="$categories" :selected="[old('category_id', $listing->category_id)]" :level="0" />
                    </select>
                                    @error('category_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                </div>
            </div>

            @php
                $lid = $defaultLanguage->id;
                $descVals = old('descriptions', $descriptions);
                $d = $descVals[$lid] ?? ['title' => '', 'description' => ''];
            @endphp
            <div class="mb-8">
                <h3 class="text-lg font-bold text-[#1A1A1A] mb-6" style="font-family: Inter, sans-serif;">{{ __('listings.listing_info') }}</h3>
                <div class="space-y-6">
                    <div>
                        <label for="title_{{ $lid }}" class="block text-sm font-semibold text-[#1A1A1A] mb-2">{{ __('components/language-tabs.listing_title') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="descriptions[{{ $lid }}][title]" id="title_{{ $lid }}" required maxlength="255" value="{{ $d['title'] ?? '' }}"
                               class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] @error('descriptions.'.$lid.'.title') border-red-500 @enderror">
                        @error('descriptions.'.$lid.'.title')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="description_{{ $lid }}" class="block text-sm font-semibold text-[#1A1A1A] mb-2">{{ __('components/language-tabs.listing_description') }} <span class="text-red-500">*</span></label>
                        <textarea name="descriptions[{{ $lid }}][description]" id="description_{{ $lid }}" rows="6" required
                                  class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] @error('descriptions.'.$lid.'.description') border-red-500 @enderror">{{ $d['description'] ?? '' }}</textarea>
                        @error('descriptions.'.$lid.'.description')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Price Section -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-[#1A1A1A] mb-4" style="font-family: Inter, sans-serif;">{{ __('listings.price_section') }}</h3>
                <div>
                    <label for="price" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('listings.price_label') }}</label>
                    <div class="relative">
                        <input type="number" name="price" id="price" value="{{ old('price', $listing->price) }}"
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
        </div>

        <!-- Location -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
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
                                <option value="{{ $city->id }}" {{ old('city_id', $listing->city_id) == $city->id ? 'selected' : '' }}>
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
                                class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all cursor-pointer @error('district_id') border-red-500 @enderror" required style="font-family: Inter, sans-serif;">
                            <option value="">{{ __('listings.district_placeholder') }}</option>
                            @foreach($districts as $district)
                                <option value="{{ $district->id }}" {{ old('district_id', $listing->district_id) == $district->id ? 'selected' : '' }}>
                                    {{ $district->name }}
                                </option>
                            @endforeach
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
                            class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all cursor-pointer @error('neighborhood_id') border-red-500 @enderror" style="font-family: Inter, sans-serif;">
                        <option value="">{{ __('listings.neighborhood_placeholder') }}</option>
                        @foreach($neighborhoods as $neighborhood)
                            <option value="{{ $neighborhood->id }}" {{ old('neighborhood_id', $listing->neighborhood_id) == $neighborhood->id ? 'selected' : '' }}>
                                {{ $neighborhood->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('neighborhood_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Category Attributes -->
        @if($attributes->count() > 0)
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <h3 class="text-lg font-bold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('listings.listing_attributes') }}</h3>
            <p class="text-sm text-[#666666] mb-6" style="font-family: Inter, sans-serif;">{{ __('listings.attributes_edit_help', ['category' => $listing->category->name]) }}</p>

            <div class="space-y-8">
                @foreach($attributes as $attribute)
                    @php
                        // Get existing attribute values for this attribute
                        $existingAttributeValues = $listing->attributeValues->filter(function($attributeValue) use ($attribute) {
                            return $attributeValue->attribute_id == $attribute->id;
                        });
                        $existingCustomValue = $listing->customAttributeValues->where('attribute_id', $attribute->id)->first();
                    @endphp
                    <div class="attribute-section">
                        <div class="mb-4">
                                <label class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">
                                    {{ $attribute->name }}
                                    @if($attribute->is_required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>

                            @if($attribute->description)
                                <p class="text-xs text-gray-500 mb-3">{{ $attribute->description }}</p>
                            @endif

                            @switch($attribute->display_type_user_panel)
                                @case('select')
                                    @if($attribute->values->count() > 0)
                                        <select name="attributes[{{ $attribute->id }}]"
                                                class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all cursor-pointer @error('attributes.'.$attribute->id) border-red-500 @enderror" style="font-family: Inter, sans-serif;"
                                                {{ $attribute->is_required ? 'required' : '' }}>
                                            <option value="">{{ __('listings.select_placeholder') }}</option>
                                            @foreach($attribute->values as $value)
                                                <option value="{{ $value->id }}"
                                                        {{ (old('attributes.'.$attribute->id) == $value->id || $existingAttributeValues->contains('id', $value->id)) ? 'selected' : '' }}>
                                                    {{ $value->value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" name="custom_attributes[{{ $attribute->id }}]"
                                               value="{{ old('custom_attributes.'.$attribute->id, $existingCustomValue ? $existingCustomValue->value : '') }}"
                                               class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all @error('custom_attributes.'.$attribute->id) border-red-500 @enderror" style="font-family: Inter, sans-serif;"
                                               placeholder="{{ $attribute->name }} değeri girin"
                                               {{ $attribute->is_required ? 'required' : '' }}>
                                    @endif
                                    @break

                                @case('checkbox')
                                    @if($attribute->values->count() > 0)
                                        @php
                                            $selectedValues = $existingAttributeValues->pluck('id')->toArray();
                                            $oldValues = old('attributes.'.$attribute->id, $selectedValues);
                                        @endphp
                                        <div class="space-y-2">
                                            @foreach($attribute->values as $value)
                                                <div class="flex items-center">
                                                    <input type="checkbox"
                                                           name="attributes[{{ $attribute->id }}][]"
                                                           value="{{ $value->id }}"
                                                           id="attr_{{ $attribute->id }}_{{ $value->id }}"
                                                           class="w-4 h-4 text-[#1E6F5C] border-[#E0E0E0] rounded focus:ring-[#1E6F5C] cursor-pointer"
                                                           {{ (is_array($oldValues) && in_array($value->id, $oldValues)) ? 'checked' : '' }}>
                                                    <label for="attr_{{ $attribute->id }}_{{ $value->id }}" class="ml-2 block text-sm text-gray-900">
                                                        {{ $value->value }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @break

                                @case('radio')
                                    @if($attribute->values->count() > 0)
                                        <div class="space-y-2">
                                            @foreach($attribute->values as $value)
                                                <div class="flex items-center">
                                                    <input type="radio"
                                                           name="attributes[{{ $attribute->id }}]"
                                                           value="{{ $value->id }}"
                                                           id="attr_{{ $attribute->id }}_{{ $value->id }}"
                                                           class="w-4 h-4 text-[#1E6F5C] border-[#E0E0E0] focus:ring-[#1E6F5C] cursor-pointer"
                                                           {{ (old('attributes.'.$attribute->id) == $value->id || $existingAttributeValues->contains('id', $value->id)) ? 'checked' : '' }}
                                                           {{ $attribute->is_required ? 'required' : '' }}>
                                                    <label for="attr_{{ $attribute->id }}_{{ $value->id }}" class="ml-2 block text-sm text-gray-900">
                                                        {{ $value->value }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @break

                                @case('number')
                                    <div class="flex items-center space-x-2">
                                        <input type="number"
                                               name="custom_attributes[{{ $attribute->id }}]"
                                               value="{{ old('custom_attributes.'.$attribute->id, $existingCustomValue ? $existingCustomValue->value : '') }}"
                                               class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all @error('custom_attributes.'.$attribute->id) border-red-500 @enderror" style="font-family: Inter, sans-serif;"
                                               placeholder="Sayı girin"
                                               {{ $attribute->is_required ? 'required' : '' }}
                                               step="any">
                                        @if($attribute->unit)
                                            <span class="text-sm text-gray-500">{{ $attribute->unit }}</span>
                                        @endif
                                    </div>
                                    @break

                                @case('textarea')
                                    <textarea name="custom_attributes[{{ $attribute->id }}]"
                                              rows="3"
                                              class="appearance-none rounded-button relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm @error('custom_attributes.'.$attribute->id) border-red-500 @enderror"
                                              placeholder="{{ $attribute->name }} detayları"
                                              {{ $attribute->is_required ? 'required' : '' }}>{{ old('custom_attributes.'.$attribute->id, $existingCustomValue ? $existingCustomValue->value : '') }}</textarea>
                                    @break

                                @default
                                    <input type="text"
                                           name="custom_attributes[{{ $attribute->id }}]"
                                           value="{{ old('custom_attributes.'.$attribute->id, $existingCustomValue ? $existingCustomValue->value : '') }}"
                                           class="appearance-none rounded-button relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm @error('custom_attributes.'.$attribute->id) border-red-500 @enderror"
                                           placeholder="{{ $attribute->name }} değeri girin"
                                           {{ $attribute->is_required ? 'required' : '' }}>
                            @endswitch

                            @error('attributes.'.$attribute->id)
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                            @error('custom_attributes.'.$attribute->id)
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Image Management -->
        <x-listing-image-manager
            :listing="$listing"
            mode="edit"
            :max-images="10"
            :required="false" />

        {{-- Map picker (optional coordinates) --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-bold text-[#1A1A1A] mb-4 flex items-center">
                <i class="ri-map-pin-2-line mr-2 text-[#1E6F5C]"></i>{{ __('listings.location_on_map') }}
            </h3>
            <x-map-picker
                name="coordinates"
                :latitude="old('coordinates.latitude', $listing->latitude)"
                :longitude="old('coordinates.longitude', $listing->longitude)" />
        </div>

        {{-- Video (optional) --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-bold text-[#1A1A1A] mb-4 flex items-center">
                <i class="ri-vidicon-line mr-2 text-[#1E6F5C]"></i>{{ __('listings.video') }}
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="video_url" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                        {{ __('listings.video_url') }}
                    </label>
                    <input type="url" name="video_url" id="video_url"
                           value="{{ old('video_url', $listing->video_url) }}"
                           placeholder="https://youtu.be/…"
                           class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] @error('video_url') border-red-500 @enderror">
                    <small class="text-gray-500">{{ __('listings.video_url_help') }}</small>
                    @error('video_url')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="video_file" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                        {{ __('listings.video_file') }}
                    </label>
                    <input type="file" name="video_file" id="video_file"
                           accept="video/mp4,video/webm,video/ogg"
                           class="block w-full text-sm">
                    <small class="text-gray-500">{{ __('listings.video_file_help', ['max' => (int) ceil(\App\Services\VideoEmbedService::maxFileBytes() / 1024 / 1024)]) }}</small>
                    @if($listing->video_file)
                        <p class="mt-2 text-xs text-gray-600">
                            <i class="ri-attachment-2 mr-1"></i>{{ basename($listing->video_file) }}
                            <label class="ml-2 inline-flex items-center text-red-600 cursor-pointer">
                                <input type="checkbox" name="remove_video_file" value="1" class="mr-1"> {{ __('listings.delete') ?? 'Sil' }}
                            </label>
                        </p>
                    @endif
                    @error('video_file')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    <p class="mb-1">{{ __('listings.update_note') }}</p>
                    <p>{{ __('listings.last_update') }}: {{ $listing->updated_at->format('d.m.Y H:i') }}</p>
                </div>

                <div class="flex space-x-3">
                    <a href="{{ route('user.listings.my') }}" class="inline-flex items-center px-6 py-3 border border-[#E0E0E0] text-sm font-bold rounded-xl text-[#1A1A1A] bg-white hover:bg-[#F5F7F8] transition-all duration-300 whitespace-nowrap cursor-pointer" style="font-family: Inter, sans-serif; font-weight: 700;">
                        {{ __('listings.cancel') }}
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl text-white bg-[#1E6F5C] hover:bg-[#13493E] transition-all duration-300 whitespace-nowrap cursor-pointer" style="font-family: Inter, sans-serif; font-weight: 700;">
                        <i class="ri-save-line mr-2"></i>
                        {{ __('listings.save_changes') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Location cascading dropdowns
document.getElementById('city_id').addEventListener('change', function() {
    const cityId = this.value;
    const districtSelect = document.getElementById('district_id');
    const neighborhoodSelect = document.getElementById('neighborhood_id');

    // Reset district and neighborhood
    districtSelect.innerHTML = '<option value="">{{ __('listings.district_placeholder') }}</option>';
    neighborhoodSelect.innerHTML = '<option value="">{{ __('listings.neighborhood_placeholder') }}</option>';

    if (cityId) {
        // Fetch districts
        fetch(`/api/cities/${cityId}/districts`)
            .then(response => response.json())
            .then(districts => {
                const currentDistrictId = '{{ old("district_id", $listing->district_id) }}';

                districts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.id;
                    option.textContent = district.name;
                    if (currentDistrictId == district.id) {
                        option.selected = true;
                    }
                    districtSelect.appendChild(option);
                });

                // Trigger district change if there's a selected district
                if (currentDistrictId && districtSelect.value) {
                    districtSelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => console.error('Error loading districts:', error));
    }
});

document.getElementById('district_id').addEventListener('change', function() {
    const districtId = this.value;
    const neighborhoodSelect = document.getElementById('neighborhood_id');

    // Reset neighborhood
    neighborhoodSelect.innerHTML = '<option value="">{{ __('listings.neighborhood_placeholder') }}</option>';

    if (districtId) {
        // Fetch neighborhoods
        fetch(`/api/districts/${districtId}/neighborhoods`)
            .then(response => response.json())
            .then(neighborhoods => {
                const currentNeighborhoodId = '{{ old("neighborhood_id", $listing->neighborhood_id) }}';

                neighborhoods.forEach(neighborhood => {
                    const option = document.createElement('option');
                    option.value = neighborhood.id;
                    option.textContent = neighborhood.name;
                    if (currentNeighborhoodId == neighborhood.id) {
                        option.selected = true;
                    }
                    neighborhoodSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading neighborhoods:', error));
    }
});

// Initialize on page load - districts and neighborhoods are already loaded from backend
// No need to trigger city change event on initial load

// Form submission - image_ids is already handled by ListingImageManager
// No additional handling needed here
</script>
@endpush
@endsection
