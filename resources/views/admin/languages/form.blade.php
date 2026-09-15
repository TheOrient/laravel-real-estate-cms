@csrf

@if(isset($language))
    @method('PUT')
@endif

<div class="form-group">
    <label for="language-title">{{ __('admin/languages.title') }}</label>
    <input type="text"
           class="form-control @error('title') is-invalid @enderror"
           id="language-title"
           name="title"
           value="{{ old('title', $language->title ?? '') }}"
           required>
    @error('title')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@if(isset($language))
    <div class="form-group">
        <label>Kod</label>
        <input type="text" class="form-control" value="{{ $language->code }}" readonly>
    </div>

    <div class="form-group">
        <label>{{ __('admin/languages.icon') }}</label>
        <div>
            @php
                $iconUrl = language_icon_url($language);
            @endphp
            @if($iconUrl)
                <img src="{{ $iconUrl }}" alt="{{ $language->title }}" width="48" height="48" class="img-circle border">
            @else
                <span class="text-muted">-</span>
            @endif
        </div>
        <small class="form-text text-muted">
            {{ __('admin/languages.icon_hint') }}
        </small>
    </div>

    <div class="form-group">
        <label for="language-sort-order">{{ __('admin/languages.sort_order') }}</label>
        <input type="number"
               class="form-control @error('sort_order') is-invalid @enderror"
               id="language-sort-order"
               name="sort_order"
               min="0"
               value="{{ old('sort_order', $language->sort_order) }}">
        @error('sort_order')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group form-check">
        <input type="checkbox"
               class="form-check-input"
               id="language-is-active"
               name="is_active"
               value="1"
               {{ old('is_active', $language->is_active) ? 'checked' : '' }}>
        <label class="form-check-label" for="language-is-active">{{ __('admin/languages.is_active') }}</label>
    </div>

    <div class="form-group form-check">
        <input type="checkbox"
               class="form-check-input"
               id="language-is-default"
               name="is_default"
               value="1"
               {{ old('is_default', $language->is_default) ? 'checked' : '' }}>
        <label class="form-check-label" for="language-is-default">{{ __('admin/languages.is_default') }}</label>
    </div>
@endif

<div class="form-group">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> {{ __('admin/general.save') }}
    </button>
    <a href="{{ route('admin.languages.index') }}" class="btn btn-secondary">
        {{ __('admin/general.cancel') }}
    </a>
</div>
