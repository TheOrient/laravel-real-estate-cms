@props([
    'languages',
    'descriptions' => [],
    'errors' => null,
    'namePrefix' => 'descriptions',
    'showSlug' => false
])

@php
    // Ensure errors is a MessageBag instance or use the global $errors
    $errorBag = $errors instanceof \Illuminate\Support\MessageBag ? $errors : ($errors ?? $__env->getShared('errors', new \Illuminate\Support\MessageBag()));
@endphp

<div x-data="{ activeTab: '{{ $languages->first()->id ?? 1 }}' }" class="w-full">
    {{-- Tab Headers --}}
    <div class="border-b border-gray-200">
        <nav class="flex space-x-4" aria-label="Tabs">
            @foreach($languages as $index => $language)
                <button
                    type="button"
                    @click="activeTab = '{{ $language->id }}'"
                    :class="activeTab === '{{ $language->id }}' ? 'border-[#1E6F5C] text-[#1E6F5C]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200 flex items-center gap-2"
                >
                    @php
                        $iconUrl = language_icon_url($language);
                    @endphp
                    @if($iconUrl)
                        <img src="{{ $iconUrl }}" alt="{{ $language->title }}" class="w-5 h-5">
                    @endif
                    <span>{{ $language->title }}</span>
                    @if($language->is_default)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-[#1E6F5C] text-white">
                            {{ __('components/language-tabs.default_language') }}
                        </span>
                    @endif
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Tab Content --}}
    <div class="mt-6">
        @foreach($languages as $language)
            @php
                $langId = $language->id;
                $descData = $descriptions[$langId] ?? [];
                $oldDescData = old("{$namePrefix}.{$langId}", $descData);
                $oldDescData = is_array($oldDescData) ? $oldDescData : [];

                $titleValue = $oldDescData['title'] ?? '';
                $slugValue = $oldDescData['slug'] ?? '';
                $descriptionValue = $oldDescData['description'] ?? '';

                $titleErrorKey = "{$namePrefix}.{$langId}.title";
                $slugErrorKey = "{$namePrefix}.{$langId}.slug";
                $descErrorKey = "{$namePrefix}.{$langId}.description";

                $hasTitleError = $errorBag && $errorBag->has($titleErrorKey);
                $hasSlugError = $errorBag && $errorBag->has($slugErrorKey);
                $hasDescError = $errorBag && $errorBag->has($descErrorKey);
            @endphp

            <div
                x-show="activeTab === '{{ $langId }}'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="space-y-6"
            >
                {{-- Title Field --}}
                <div>
                    <label for="title_{{ $langId }}" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">
                        {{ __('components/language-tabs.listing_title') }}
                        @if($language->is_default)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    <input
                        type="text"
                        name="{{ $namePrefix }}[{{ $langId }}][title]"
                        id="title_{{ $langId }}"
                        value="{{ $titleValue }}"
                        class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all {{ $hasTitleError ? 'border-red-500' : '' }}"
                        placeholder="{{ __('components/language-tabs.listing_title_placeholder') }}"
                        maxlength="255"
                        {{ $language->is_default ? 'required' : '' }}
                        style="font-family: Inter, sans-serif;"
                        x-data="{ count: 0 }"
                        x-init="count = $el.value.length"
                        @input="count = $el.value.length"
                    >
                    <div class="flex justify-between items-center mt-2">
                        <p class="text-sm text-[#666666]" style="font-family: Inter, sans-serif;">
                            @if($language->is_default)
                                {{ __('components/language-tabs.required_field') }}
                            @else
                                {{ __('components/language-tabs.optional_field') }}
                            @endif
                        </p>
                        <p class="text-sm text-[#666666]" style="font-family: Inter, sans-serif;" x-text="`${count}/255`"></p>
                    </div>
                    @if($hasTitleError)
                        <p class="mt-1 text-sm text-red-500">{{ $errorBag->first($titleErrorKey) }}</p>
                    @endif
                </div>

                {{-- Slug Field (Optional) --}}
                @if($showSlug)
                <div>
                    <label for="slug_{{ $langId }}" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">
                        {{ __('components/language-tabs.url_slug') }}
                    </label>
                    <input
                        type="text"
                        name="{{ $namePrefix }}[{{ $langId }}][slug]"
                        id="slug_{{ $langId }}"
                        value="{{ $slugValue }}"
                        class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all {{ $hasSlugError ? 'border-red-500' : '' }}"
                        placeholder="{{ __('components/language-tabs.url_slug_placeholder') }}"
                        style="font-family: Inter, sans-serif;"
                    >
                    <p class="mt-2 text-sm text-[#666666]" style="font-family: Inter, sans-serif;">
                        {{ __('components/language-tabs.url_slug_help') }}
                    </p>
                    @if($hasSlugError)
                        <p class="mt-1 text-sm text-red-500">{{ $errorBag->first($slugErrorKey) }}</p>
                    @endif
                </div>
                @endif

                {{-- Description Field --}}
                <div>
                    <label for="description_{{ $langId }}" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">
                        {{ __('components/language-tabs.listing_description') }}
                        @if($language->is_default)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    <textarea
                        name="{{ $namePrefix }}[{{ $langId }}][description]"
                        id="description_{{ $langId }}"
                        rows="6"
                        class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all {{ $hasDescError ? 'border-red-500' : '' }}"
                        placeholder="{{ __('components/language-tabs.listing_description_placeholder') }}"
                        {{ $language->is_default ? 'required' : '' }}
                        style="font-family: Inter, sans-serif;"
                    >{{ $descriptionValue }}</textarea>
                    <p class="mt-2 text-sm text-[#666666]" style="font-family: Inter, sans-serif;">
                        @if($language->is_default)
                            {{ __('components/language-tabs.required_field') }}
                        @else
                            {{ __('components/language-tabs.optional_field') }}
                        @endif
                    </p>
                    @if($hasDescError)
                        <p class="mt-1 text-sm text-red-500">{{ $errorBag->first($descErrorKey) }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('head')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
