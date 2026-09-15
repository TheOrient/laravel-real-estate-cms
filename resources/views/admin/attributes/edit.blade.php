@extends('admin.layouts.app')

@section('title', __('admin/attributes.edit_attribute'))

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $attribute->name }} - {{ __('admin/attributes.edit_attribute') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.attributes.index') }}">{{ __('admin/attributes.attributes') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin/attributes.edit_attribute') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $attribute->name }} - {{ __('admin/attributes.edit_attribute') }}</h1>
        <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">
            <i class="ri-arrow-left-line"></i> {{ __('admin/general.back') }}
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.attributes.update', $attribute) }}" method="POST" id="attributeForm">
                        @csrf
                        @method('PUT')

                        <!-- Dil Sekmeleri -->
                        <div class="form-group">
                            <label>{{ __('admin/attributes.translations') }}</label>
                            <ul class="nav nav-tabs" id="languageTabs" role="tablist">
                                @foreach($languages as $index => $language)
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                           id="lang-{{ $language->id }}-tab"
                                           data-toggle="tab"
                                           href="#lang-{{ $language->id }}"
                                           role="tab"
                                           aria-controls="lang-{{ $language->id }}"
                                           aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                            @php
                                                $iconUrl = language_icon_url($language);
                                            @endphp
                                            @if($iconUrl)
                                                <img src="{{ $iconUrl }}" alt="{{ $language->title }}" width="20" height="20" class="mr-1" style="vertical-align: middle;">
                                            @endif
                                            {{ $language->title }}
                                            @if($language->is_default)
                                                <span class="badge badge-primary badge-sm ml-1">{{ __('admin/general.default') }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="tab-content" id="languageTabContent">
                                @foreach($languages as $index => $language)
                                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                         id="lang-{{ $language->id }}"
                                         role="tabpanel"
                                         aria-labelledby="lang-{{ $language->id }}-tab">
                                        <div class="border-top pt-3">
                                            <input type="hidden" name="descriptions[{{ $language->id }}][language_id]" value="{{ $language->id }}">

                                            <div class="form-group">
                                                <label for="name_{{ $language->id }}">{{ __('admin/attributes.name') }} <span class="text-danger">*</span></label>
                                                <input type="text"
                                                       class="form-control @error('descriptions.' . $language->id . '.name') is-invalid @enderror"
                                                       id="name_{{ $language->id }}"
                                                       name="descriptions[{{ $language->id }}][name]"
                                                       value="{{ old('descriptions.' . $language->id . '.name', $descriptions[$language->id]['name'] ?? '') }}"
                                                       {{ $language->is_default ? 'required' : '' }}>
                                                @error('descriptions.' . $language->id . '.name')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="input_placeholder_{{ $language->id }}">{{ __('admin/attributes.input_placeholder') }}</label>
                                                <input type="text"
                                                       class="form-control @error('descriptions.' . $language->id . '.input_placeholder') is-invalid @enderror"
                                                       id="input_placeholder_{{ $language->id }}"
                                                       name="descriptions[{{ $language->id }}][input_placeholder]"
                                                       value="{{ old('descriptions.' . $language->id . '.input_placeholder', $descriptions[$language->id]['input_placeholder'] ?? '') }}"
                                                       placeholder="{{ __('admin/attributes.input_placeholder_help') }}">
                                                @error('descriptions.' . $language->id . '.input_placeholder')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label for="display_type_search">{{ __('admin/attributes.display_type_search') }} *</label>
                            <select name="display_type_search" id="display_type_search" class="form-control @error('display_type_search') is-invalid @enderror" required>
                                <option value="">{{ __('admin/general.all') }}</option>
                                <option value="input" {{ old('display_type_search', $attribute->display_type_search) == 'input' ? 'selected' : '' }}>{{ __('admin/attributes.display_type_input') }}</option>
                                <option value="number" {{ old('display_type_search', $attribute->display_type_search) == 'number' ? 'selected' : '' }}>{{ __('admin/attributes.display_type_number') }}</option>
                                <option value="select" {{ old('display_type_search', $attribute->display_type_search) == 'select' ? 'selected' : '' }}>{{ __('admin/attributes.display_type_select') }}</option>
                                <option value="multi_select" {{ old('display_type_search', $attribute->display_type_search) == 'multi_select' ? 'selected' : '' }}>{{ __('admin/attributes.display_type_multi_select') }}</option>
                                <option value="radio" {{ old('display_type_search', $attribute->display_type_search) == 'radio' ? 'selected' : '' }}>{{ __('admin/attributes.display_type_radio') }}</option>
                                <option value="checkbox" {{ old('display_type_search', $attribute->display_type_search) == 'checkbox' ? 'selected' : '' }}>{{ __('admin/attributes.display_type_checkbox') }}</option>
                                <option value="textarea" {{ old('display_type_search', $attribute->display_type_search) == 'textarea' ? 'selected' : '' }}>{{ __('admin/attributes.display_type_textarea') }}</option>
                            </select>
                            @error('display_type_search')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="display_type_user_panel">{{ __('admin/attributes.display_type_user_panel') }} *</label>
                            <select name="display_type_user_panel" id="display_type_user_panel" class="form-control @error('display_type_user_panel') is-invalid @enderror" required>
                                <option value="">{{ __('admin/general.all') }}</option>
                                <option value="input" {{ old('display_type_user_panel', $attribute->display_type_user_panel) == 'input' ? 'selected' : '' }}>{{ __('admin/attributes.display_type_input') }}</option>
                                <option value="number" {{ old('display_type_user_panel', $attribute->display_type_user_panel) == 'number' ? 'selected' : '' }}>{{ __('admin/attributes.display_type_number') }}</option>
                                <option value="select" {{ old('display_type_user_panel', $attribute->display_type_user_panel) == 'select' ? 'selected' : '' }}>{{ __('admin/attributes.display_type_select') }}</option>
                                <option value="multi_select" {{ old('display_type_user_panel', $attribute->display_type_user_panel) == 'multi_select' ? 'selected' : '' }}>{{ __('admin/attributes.display_type_multi_select') }}</option>
                                <option value="radio" {{ old('display_type_user_panel', $attribute->display_type_user_panel) == 'radio' ? 'selected' : '' }}>{{ __('admin/attributes.display_type_radio') }}</option>
                                <option value="checkbox" {{ old('display_type_user_panel', $attribute->display_type_user_panel) == 'checkbox' ? 'selected' : '' }}>{{ __('admin/attributes.display_type_checkbox') }}</option>
                                <option value="textarea" {{ old('display_type_user_panel', $attribute->display_type_user_panel) == 'textarea' ? 'selected' : '' }}>{{ __('admin/attributes.display_type_textarea') }}</option>
                            </select>
                            @error('display_type_user_panel')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="min_value">{{ __('admin/attributes.min_value') }}</label>
                                    <input type="number" name="min_value" id="min_value" class="form-control @error('min_value') is-invalid @enderror"
                                           value="{{ old('min_value', $attribute->min_value) }}">
                                    @error('min_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_value">{{ __('admin/attributes.max_value') }}</label>
                                    <input type="number" name="max_value" id="max_value" class="form-control @error('max_value') is-invalid @enderror"
                                           value="{{ old('max_value', $attribute->max_value) }}">
                                    @error('max_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="is_required" id="is_required" class="form-check-input"
                                       value="1" {{ old('is_required', $attribute->is_required) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_required">
                                    {{ __('admin/attributes.is_required') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="is_filterable" id="is_filterable" class="form-check-input"
                                       value="1" {{ old('is_filterable', $attribute->is_filterable) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_filterable">
                                    {{ __('admin/attributes.is_filterable') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="show_in_listing" id="show_in_listing" class="form-check-input"
                                       value="1" {{ old('show_in_listing', $attribute->show_in_listing) ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_in_listing">
                                    {{ __('admin/attributes.show_in_listing') }}
                                </label>
                            </div>
                        </div>

                        <!-- Attribute Values (for select, radio, checkbox types) -->
                        <div id="attributeValues" class="{{ in_array($attribute->display_type_user_panel, ['select', 'multi_select', 'radio', 'checkbox']) ? '' : 'd-none' }}">
                            <hr>
                            <h5>{{ __('admin/attributes.attribute_values') }}</h5>
                            <p class="text-muted small">{{ __('admin/attributes.attribute_values_help') }}</p>
                            <div id="valuesContainer">
                                @php
                                    $existingCount = $attribute->values ? $attribute->values->count() : 0;
                                    $maxValues = max(10, $existingCount + 5);
                                @endphp

                                @if($attribute->values && $attribute->values->count() > 0)
                                    @foreach($attribute->values as $valueIndex => $value)
                                        <div class="card mb-3 value-item" data-value-index="{{ $valueIndex }}" data-value-id="{{ $value->id }}">
                                            <div class="card-body">
                                                <input type="hidden" name="value_descriptions[{{ $valueIndex }}][id]" value="{{ $value->id }}">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0 value-title">{{ __('admin/attributes.value') }} #{{ $valueIndex + 1 }}</h6>
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-value-btn">
                                                        <i class="ri-delete-bin-line"></i> {{ __('admin/general.delete') }}
                                                    </button>
                                                </div>
                                                <ul class="nav nav-tabs" role="tablist">
                                                    @foreach($languages as $langIndex => $language)
                                                        <li class="nav-item">
                                                            <a class="nav-link {{ $langIndex === 0 ? 'active' : '' }}"
                                                               id="value-{{ $valueIndex }}-lang-{{ $language->id }}-tab"
                                                               data-toggle="tab"
                                                               href="#value-{{ $valueIndex }}-lang-{{ $language->id }}"
                                                               role="tab">
                                                                @php
                                                                    $iconUrl = language_icon_url($language);
                                                                @endphp
                                                                @if($iconUrl)
                                                                    <img src="{{ $iconUrl }}" alt="{{ $language->title }}" width="16" height="16" class="mr-1">
                                                                @endif
                                                                {{ $language->title }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                <div class="tab-content mt-2">
                                                    @foreach($languages as $langIndex => $language)
                                                        @php
                                                            $valueDesc = $value->descriptions->where('language_id', $language->id)->first();
                                                            $valueText = $valueDesc ? $valueDesc->value : '';
                                                        @endphp
                                                        <div class="tab-pane fade {{ $langIndex === 0 ? 'show active' : '' }}"
                                                             id="value-{{ $valueIndex }}-lang-{{ $language->id }}"
                                                             role="tabpanel">
                                                            <input type="hidden" name="value_descriptions[{{ $valueIndex }}][{{ $language->id }}][language_id]" value="{{ $language->id }}">
                                                            <input type="text"
                                                                   name="value_descriptions[{{ $valueIndex }}][{{ $language->id }}][value]"
                                                                   class="form-control"
                                                                   placeholder="{{ __('admin/attributes.enter_value') }}"
                                                                   value="{{ old('value_descriptions.' . $valueIndex . '.' . $language->id . '.value', $valueDescriptions[$valueIndex][$language->id]['value'] ?? $valueText) }}"
                                                                   {{ $langIndex === 0 ? 'required' : '' }}>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                @for($i = $existingCount; $i < $maxValues; $i++)
                                    <div class="card mb-3 value-item" data-value-index="{{ $i }}" style="display: none;">
                                        <div class="card-body">
                                            <input type="hidden" name="value_descriptions[{{ $i }}][id]" value="">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0">{{ __('admin/attributes.value') }} #{{ $i + 1 }}</h6>
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-value-btn">
                                                    <i class="ri-delete-bin-line"></i> {{ __('admin/general.delete') }}
                                                </button>
                                            </div>
                                            <ul class="nav nav-tabs" role="tablist">
                                                @foreach($languages as $langIndex => $language)
                                                    <li class="nav-item">
                                                        <a class="nav-link {{ $langIndex === 0 ? 'active' : '' }}"
                                                           id="value-{{ $i }}-lang-{{ $language->id }}-tab"
                                                           data-toggle="tab"
                                                           href="#value-{{ $i }}-lang-{{ $language->id }}"
                                                           role="tab">
                                                            @php
                                                                $iconUrl = language_icon_url($language);
                                                            @endphp
                                                            @if($iconUrl)
                                                                <img src="{{ $iconUrl }}" alt="{{ $language->title }}" width="16" height="16" class="mr-1">
                                                            @endif
                                                            {{ $language->title }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <div class="tab-content mt-2">
                                                @foreach($languages as $langIndex => $language)
                                                    <div class="tab-pane fade {{ $langIndex === 0 ? 'show active' : '' }}"
                                                         id="value-{{ $i }}-lang-{{ $language->id }}"
                                                         role="tabpanel">
                                                        <input type="hidden" name="value_descriptions[{{ $i }}][{{ $language->id }}][language_id]" value="{{ $language->id }}">
                                                        <input type="text"
                                                               name="value_descriptions[{{ $i }}][{{ $language->id }}][value]"
                                                               class="form-control"
                                                               placeholder="{{ __('admin/attributes.enter_value') }}"
                                                               {{ $langIndex === 0 ? 'required' : '' }}>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-primary" id="addValue">
                                <i class="ri-add-line"></i> {{ __('admin/attributes.add_value') }}
                            </button>
                        </div>

                        <hr>
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> {{ __('admin/general.update') }}
                            </button>
                            <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">{{ __('admin/general.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5>Özellik Bilgileri</h5>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td>{{ $attribute->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Oluşturulma:</strong></td>
                            <td>{{ $attribute->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Güncellenme:</strong></td>
                            <td>{{ $attribute->updated_at->format('d.m.Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Değer Sayısı:</strong></td>
                            <td>{{ $attribute->values ? $attribute->values->count() : 0 }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5>Yardım</h5>
                    <div class="alert alert-warning">
                        <h6>Dikkat!</h6>
                        <p class="mb-0">Görünüm tipini değiştirirseniz, bazı veriler kaybolabilir. Bu işlem geri alınamaz.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const displayTypeUserPanel = document.getElementById('display_type_user_panel');
    const attributeValues = document.getElementById('attributeValues');
    const valuesContainer = document.getElementById('valuesContainer');
    const addValueBtn = document.getElementById('addValue');

    const valueItems = document.querySelectorAll('.value-item');
    let visibleCount = {{ $attribute->values ? $attribute->values->count() : 0 }};

    // Show/hide values section based on display type
    function toggleValuesSection() {
        const type = displayTypeUserPanel.value;
        if (['select', 'multi_select', 'radio', 'checkbox'].includes(type)) {
            attributeValues.classList.remove('d-none');
        } else {
            attributeValues.classList.add('d-none');
        }
    }

    displayTypeUserPanel.addEventListener('change', toggleValuesSection);

    // Show next hidden value
    function showNextValue() {
        for (let i = 0; i < valueItems.length; i++) {
            if (valueItems[i].style.display === 'none' || !valueItems[i].style.display) {
                valueItems[i].style.display = 'block';
                visibleCount++;
                return;
            }
        }
    }

    // Add value button
    addValueBtn.addEventListener('click', function() {
        showNextValue();
    });

    // Remove value buttons
    document.querySelectorAll('.remove-value-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const card = this.closest('.value-item');
            card.style.display = 'none';
            visibleCount--;
            // Clear inputs
            card.querySelectorAll('input[type="text"]').forEach(function(input) {
                input.value = '';
            });
        });
    });
});
</script>
@endpush
