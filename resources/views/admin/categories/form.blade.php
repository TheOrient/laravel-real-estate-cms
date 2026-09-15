<!-- Category Form Fields -->
<div class="card-body">
    <!-- Genel Ayarlar -->
    <div class="form-group">
        <label for="parent_id">{{ __('admin/categories.parent_category') }}</label>
        <select class="form-control @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
            <option value="">{{ __('admin/categories.no_parent') }}</option>
            @foreach ($categories as $cat)
                @if(isset($category) && $cat->id === $category->id)
                    @continue
                @endif
                @php
                    $catName = $cat->getCurrentDescription() ? $cat->getCurrentDescription()->name : 'Kategori #' . $cat->id;
                @endphp
                <option value="{{ $cat->id }}" @selected(old('parent_id', $category->parent_id ?? null) == $cat->id)>
                    {{ $catName }}
                </option>
            @endforeach
        </select>
        @error('parent_id')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="icon">{{ __('admin/categories.icon') }}</label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="ri-information-line"></i></span>
            </div>
            <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" value="{{ old('icon', $category->icon ?? '') }}" placeholder="{{ __('admin/categories.icon_placeholder') }}">
        </div>
        <small class="form-text text-muted">
            <a href="https://remixicon.com/" target="_blank">Remix Icon</a> {{ __('admin/categories.icon_info') }}
        </small>
        @error('icon')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="is_filterable" name="is_filterable" value="1"
                @checked(old('is_filterable', $category->is_filterable ?? false))>
            <label class="custom-control-label" for="is_filterable">{{ __('admin/categories.is_filterable') }}</label>
        </div>
        <small class="form-text text-muted">{{ __('admin/categories.filterable_info') }}</small>
    </div>

    <div class="form-group">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                @checked(old('is_active', $category->is_active ?? true))>
            <label class="custom-control-label" for="is_active">{{ __('admin/general.active') }}</label>
        </div>
    </div>

    <hr>

    <!-- Dil Sekmeleri -->
    <div class="form-group">
        <label>{{ __('admin/categories.translations') }}</label>
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
                    <div class="card-body border-top">
                        <input type="hidden" name="descriptions[{{ $language->id }}][language_id]" value="{{ $language->id }}">

                        <div class="form-group">
                            <label for="name_{{ $language->id }}">{{ __('admin/categories.name') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text"
                                       class="form-control @error("descriptions.{$language->id}.name") is-invalid @enderror"
                                       id="name_{{ $language->id }}"
                                       name="descriptions[{{ $language->id }}][name]"
                                       value="{{ old("descriptions.{$language->id}.name", $descriptions[$language->id]['name'] ?? '') }}"
                                       {{ $language->is_default ? 'required' : '' }}>
                                @if(!$language->is_default)
                                    <div class="input-group-append">
                                        <button type="button"
                                                class="btn btn-info btn-translate"
                                                data-input-id="name_{{ $language->id }}"
                                                data-target-language="{{ $language->code }}"
                                                data-field-type="name"
                                                title="{{ __('admin/general.translate') }}">
                                            <i class="fas fa-language"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <small class="form-text text-muted">{{ __('admin/categories.slug_auto_generated') }}</small>
                            @error("descriptions.{$language->id}.name")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="short_description_{{ $language->id }}">{{ __('admin/categories.short_description') }}</label>
                            <div class="input-group">
                                <input type="text"
                                       class="form-control @error("descriptions.{$language->id}.short_description") is-invalid @enderror"
                                       id="short_description_{{ $language->id }}"
                                       name="descriptions[{{ $language->id }}][short_description]"
                                       value="{{ old("descriptions.{$language->id}.short_description", $descriptions[$language->id]['short_description'] ?? '') }}">
                                @if(!$language->is_default)
                                    <div class="input-group-append">
                                        <button type="button"
                                                class="btn btn-info btn-translate"
                                                data-input-id="short_description_{{ $language->id }}"
                                                data-target-language="{{ $language->code }}"
                                                data-field-type="short_description"
                                                title="{{ __('admin/general.translate') }}">
                                            <i class="fas fa-language"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            @error("descriptions.{$language->id}.short_description")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description_{{ $language->id }}">{{ __('admin/categories.description') }}</label>
                            <div class="position-relative">
                                <textarea class="form-control @error("descriptions.{$language->id}.description") is-invalid @enderror"
                                          id="description_{{ $language->id }}"
                                          name="descriptions[{{ $language->id }}][description]"
                                          rows="3"
                                          @if(!$language->is_default) style="padding-right: 50px;" @endif>{{ old("descriptions.{$language->id}.description", $descriptions[$language->id]['description'] ?? '') }}</textarea>
                                @if(!$language->is_default)
                                    <button type="button"
                                            class="btn btn-info btn-translate position-absolute"
                                            data-input-id="description_{{ $language->id }}"
                                            data-target-language="{{ $language->code }}"
                                            data-field-type="description"
                                            title="{{ __('admin/general.translate') }}"
                                            style="top: 0; right: 0; border-top-left-radius: 0; border-bottom-left-radius: 0; height: 38px;">
                                        <i class="fas fa-language"></i>
                                    </button>
                                @endif
                            </div>
                            @error("descriptions.{$language->id}.description")
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
<div class="card-footer">
    <button type="submit" class="btn btn-primary">{{ __('admin/general.save') }}</button>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">{{ __('admin/general.cancel') }}</a>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Find default language ID
    let defaultLanguageId = null;
    @foreach($languages as $language)
        @if($language->is_default)
            defaultLanguageId = {{ $language->id }};
        @endif
    @endforeach

    if (!defaultLanguageId) {
        return; // No default language found
    }

    // Get default language inputs
    const defaultNameInput = document.getElementById('name_' + defaultLanguageId);
    const defaultShortDescriptionInput = document.getElementById('short_description_' + defaultLanguageId);
    const defaultDescriptionInput = document.getElementById('description_' + defaultLanguageId);

    // Translation enabled check
    const translationEnabled = @json(\App\Models\Setting::get('translation_enabled', false));

    if (!translationEnabled) {
        // Hide all translate buttons if translation is disabled
        document.querySelectorAll('.btn-translate').forEach(btn => {
            btn.style.display = 'none';
        });
        return;
    }

    // Handle translate button clicks
    document.querySelectorAll('.btn-translate').forEach(button => {
        button.addEventListener('click', function() {
            const inputId = this.getAttribute('data-input-id');
            const targetLanguageCode = this.getAttribute('data-target-language');
            const fieldType = this.getAttribute('data-field-type');
            const targetInput = document.getElementById(inputId);

            if (!targetInput) {
                return;
            }

            // Get source text from default language input based on field type
            let sourceText = '';
            if (fieldType === 'name' && defaultNameInput) {
                sourceText = defaultNameInput.value.trim();
            } else if (fieldType === 'short_description' && defaultShortDescriptionInput) {
                sourceText = defaultShortDescriptionInput.value.trim();
            } else if (fieldType === 'description' && defaultDescriptionInput) {
                sourceText = defaultDescriptionInput.value.trim();
            }

            if (!sourceText) {
                alert('{{ __('admin/general.translation_failed') }}');
                return;
            }

            // Disable button and show loading state
            const originalHtml = this.innerHTML;
            const originalTitle = this.getAttribute('title');
            this.disabled = true;
            this.setAttribute('title', '{{ __('admin/general.translating') }}');
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            // Make API request
            fetch('{{ route('admin.translations.translate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    text: sourceText,
                    target_language_code: targetLanguageCode,
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.translated_text) {
                    targetInput.value = data.translated_text;
                    // Trigger input event for any listeners
                    targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                } else {
                    alert(data.message || '{{ __('admin/general.translation_failed') }}');
                }
            })
            .catch(error => {
                console.error('Translation error:', error);
                alert('{{ __('admin/general.translation_failed') }}');
            })
            .finally(() => {
                // Re-enable button
                this.disabled = false;
                this.innerHTML = originalHtml;
                this.setAttribute('title', originalTitle);
            });
        });
    });
});
</script>
@endpush
