@extends('layouts.app')

@section('title', __('listings.create_step3_title'))

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('listings.create_listing') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('listings.step3_attributes') }}</p>
            <p class="mt-1 text-xs text-blue-600">{{ __('listings.category') }}: {{ $category->name }}</p>
        </div>

        <!-- Progress Steps -->
        <x-listing-create-progress :current-step="3" />
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto mt-10">
    <form action="{{ route('user.listings.create.step3.store') }}" method="POST" id="attributesForm">
        @csrf

        <div class="bg-white rounded-2xl shadow-lg p-8">
            @if($attributes->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('listings.listing_attributes') }}</h3>
                    <p class="text-sm text-gray-600">{{ __('listings.attributes_help', ['category' => $category->name]) }}</p>
                </div>

                <div class="space-y-8">
                    @foreach($attributes as $attribute)
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
                                                    class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all cursor-pointer @error('attributes.'.$attribute->id) border-red-500 @enderror"
                                                    {{ $attribute->is_required ? 'required' : '' }} style="font-family: Inter, sans-serif;">
                                                <option value="">{{ __('listings.select_placeholder') }}</option>
                                                @foreach($attribute->values as $value)
                                                    <option value="{{ $value->id }}"
                                                            {{ old('attributes.'.$attribute->id) == $value->id ? 'selected' : '' }}>
                                                        {{ $value->value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" name="custom_attributes[{{ $attribute->id }}]"
                                                   value="{{ old('custom_attributes.'.$attribute->id) }}"
                                                   class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all @error('custom_attributes.'.$attribute->id) border-red-500 @enderror"
                                                   placeholder="{{ __('listings.enter_value', ['attribute' => $attribute->name]) }}"
                                                   {{ $attribute->is_required ? 'required' : '' }} style="font-family: Inter, sans-serif;">
                                        @endif
                                        @break

                                    @case('checkbox')
                                        @if($attribute->values->count() > 0)
                                            <div class="space-y-2">
                                                @foreach($attribute->values as $value)
                                                    <div class="flex items-center">
                                                        <input type="checkbox"
                                                               name="attributes[{{ $attribute->id }}][]"
                                                               value="{{ $value->id }}"
                                                               id="attr_{{ $attribute->id }}_{{ $value->id }}"
                                                               class="w-4 h-4 text-[#1E6F5C] border-[#E0E0E0] rounded focus:ring-[#1E6F5C] cursor-pointer"
                                                               {{ is_array(old('attributes.'.$attribute->id)) && in_array($value->id, old('attributes.'.$attribute->id)) ? 'checked' : '' }}>
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
                                                               {{ old('attributes.'.$attribute->id) == $value->id ? 'checked' : '' }}
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
                                                   value="{{ old('custom_attributes.'.$attribute->id) }}"
                                                   class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all @error('custom_attributes.'.$attribute->id) border-red-500 @enderror"
                                                   placeholder="{{ __('listings.enter_number') }}" style="font-family: Inter, sans-serif;"
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
                                                  class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all @error('custom_attributes.'.$attribute->id) border-red-500 @enderror" style="font-family: Inter, sans-serif;"
                                                  placeholder="{{ $attribute->name }} {{ __('listings.enter_details') }}"
                                                  {{ $attribute->is_required ? 'required' : '' }}>{{ old('custom_attributes.'.$attribute->id) }}</textarea>
                                        @break

                                    @default
                                        <input type="text"
                                               name="custom_attributes[{{ $attribute->id }}]"
                                               value="{{ old('custom_attributes.'.$attribute->id) }}"
                                               class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all @error('custom_attributes.'.$attribute->id) border-red-500 @enderror"
                                               placeholder="{{ __('listings.enter_value', ['attribute' => $attribute->name]) }}"
                                               {{ $attribute->is_required ? 'required' : '' }} style="font-family: Inter, sans-serif;">
                                @endswitch

                                @error('attributes.'.$attribute->id)
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('custom_attributes.'.$attribute->id)
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                        <i class="ri-check-line text-green-600"></i>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('listings.no_attributes_for_category') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ __('listings.no_attributes_description', ['category' => $category->name]) }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-6 flex justify-between">
            <a href="{{ route('user.listings.create.step2') }}" class="inline-flex items-center px-6 py-3 border border-[#E0E0E0] text-sm font-bold rounded-xl text-[#1A1A1A] bg-white hover:bg-[#F5F7F8] transition-all duration-300 whitespace-nowrap cursor-pointer" style="font-family: Inter, sans-serif; font-weight: 700;">
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
// Form validation for required attributes
document.getElementById('attributesForm').addEventListener('submit', function(e) {
    let isValid = true;
    const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('border-red-300');
        } else {
            field.classList.remove('border-red-300');
        }
    });

    // Check required checkboxes and radios
    const requiredGroups = document.querySelectorAll('input[type="checkbox"][required], input[type="radio"][required]');
    const checkedGroups = new Set();

    requiredGroups.forEach(input => {
        const name = input.name;
        if (!checkedGroups.has(name)) {
            const groupInputs = document.querySelectorAll(`input[name="${name}"]`);
            const hasChecked = Array.from(groupInputs).some(inp => inp.checked);

            if (!hasChecked) {
                isValid = false;
                groupInputs.forEach(inp => {
                    const container = inp.closest('.attribute-section');
                    if (container) {
                        container.classList.add('border-l-4', 'border-red-400', 'pl-4');
                    }
                });
            } else {
                groupInputs.forEach(inp => {
                    const container = inp.closest('.attribute-section');
                    if (container) {
                        container.classList.remove('border-l-4', 'border-red-400', 'pl-4');
                    }
                });
            }
            checkedGroups.add(name);
        }
    });

    if (!isValid) {
        e.preventDefault();
        alert('{{ __('listings.please_fill_required_fields') }}');

        // Scroll to first invalid field
        const firstInvalid = document.querySelector('.border-red-300, .border-red-400');
        if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});

// Real-time validation feedback
document.addEventListener('input', function(e) {
    if (e.target.hasAttribute('required')) {
        if (e.target.value.trim()) {
            e.target.classList.remove('border-red-300');
        }
    }
});

document.addEventListener('change', function(e) {
    if (e.target.type === 'checkbox' || e.target.type === 'radio') {
        if (e.target.hasAttribute('required')) {
            const name = e.target.name;
            const groupInputs = document.querySelectorAll(`input[name="${name}"]`);
            const hasChecked = Array.from(groupInputs).some(inp => inp.checked);

            if (hasChecked) {
                groupInputs.forEach(inp => {
                    const container = inp.closest('.attribute-section');
                    if (container) {
                        container.classList.remove('border-l-4', 'border-red-400', 'pl-4');
                    }
                });
            }
        }
    }
});
</script>
@endpush
@endsection