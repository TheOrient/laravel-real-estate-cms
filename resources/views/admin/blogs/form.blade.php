{{-- Shared blog post form, included from create.blade.php and edit.blade.php --}}
@php
    /** @var \App\Models\Blog|null $blog */
    $isEdit = isset($blog) && $blog->exists;
@endphp

<div class="card-body">
    <div class="row">
        {{-- ============ Left column: per-language tabs ============ --}}
        <div class="col-md-8">
            <ul class="nav nav-tabs" id="languageTabs" role="tablist">
                @foreach($languages as $index => $language)
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $index === 0 ? 'active' : '' }}"
                           id="lang-{{ $language->id }}-tab"
                           data-toggle="tab"
                           href="#lang-{{ $language->id }}"
                           role="tab">
                            @php $iconUrl = function_exists('language_icon_url') ? language_icon_url($language) : null; @endphp
                            @if($iconUrl)
                                <img src="{{ $iconUrl }}" alt="" width="20" height="20" class="mr-1" style="vertical-align:middle;">
                            @endif
                            {{ $language->title }}
                            @if($language->is_default)
                                <span class="badge badge-primary ml-1">{{ __('admin/general.default') }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content border-left border-right border-bottom p-3">
                @foreach($languages as $index => $language)
                    @php
                        $oldDesc = old("descriptions.{$language->id}", $descriptions[$language->id] ?? []);
                    @endphp
                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                         id="lang-{{ $language->id }}" role="tabpanel">

                        <div class="form-group">
                            <label>{{ __('admin/blog.title') }}
                                @if($language->is_default)<span class="text-danger">*</span>@endif
                            </label>
                            <input type="text"
                                   name="descriptions[{{ $language->id }}][title]"
                                   value="{{ $oldDesc['title'] ?? '' }}"
                                   class="form-control @error("descriptions.{$language->id}.title") is-invalid @enderror"
                                   {{ $language->is_default ? 'required' : '' }}>
                            @error("descriptions.{$language->id}.title")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>{{ __('admin/blog.slug') }}</label>
                            <input type="text"
                                   name="descriptions[{{ $language->id }}][slug]"
                                   value="{{ $oldDesc['slug'] ?? '' }}"
                                   class="form-control @error("descriptions.{$language->id}.slug") is-invalid @enderror"
                                   placeholder="{{ __('admin/blog.slug_help') }}">
                            <small class="form-text text-muted">{{ __('admin/blog.slug_help') }}</small>
                            @error("descriptions.{$language->id}.slug")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>{{ __('admin/blog.excerpt') }}</label>
                            <textarea name="descriptions[{{ $language->id }}][excerpt]"
                                      id="excerpt_{{ $language->id }}"
                                      rows="2"
                                      class="form-control @error("descriptions.{$language->id}.excerpt") is-invalid @enderror">{{ $oldDesc['excerpt'] ?? '' }}</textarea>
                            <small class="form-text text-muted">{{ __('admin/blog.excerpt_help') }}</small>
                            @error("descriptions.{$language->id}.excerpt")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="mb-0">{{ __('admin/blog.body') }}</label>
                                <button type="button"
                                        class="btn btn-sm btn-outline-info ai-generate-btn"
                                        data-target-id="body_{{ $language->id }}"
                                        data-title-id="title_{{ $language->id }}"
                                        data-language="{{ $language->code }}">
                                    <i class="fas fa-magic mr-1"></i>{{ __('admin/ai.generate_description') }}
                                </button>
                            </div>
                            <textarea name="descriptions[{{ $language->id }}][body]"
                                      id="body_{{ $language->id }}"
                                      rows="14"
                                      class="form-control summernote @error("descriptions.{$language->id}.body") is-invalid @enderror">{{ $oldDesc['body'] ?? '' }}</textarea>
                            @error("descriptions.{$language->id}.body")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>
                        <h6 class="text-muted">{{ __('admin/blog.seo') }}</h6>

                        <div class="form-group">
                            <label>{{ __('admin/blog.meta_title') }}</label>
                            <input type="text"
                                   name="descriptions[{{ $language->id }}][meta_title]"
                                   value="{{ $oldDesc['meta_title'] ?? '' }}"
                                   class="form-control">
                        </div>

                        <div class="form-group">
                            <label>{{ __('admin/blog.meta_description') }}</label>
                            <textarea name="descriptions[{{ $language->id }}][meta_description]"
                                      rows="2"
                                      class="form-control">{{ $oldDesc['meta_description'] ?? '' }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>{{ __('admin/blog.meta_keywords') }}</label>
                            <input type="text"
                                   name="descriptions[{{ $language->id }}][meta_keywords]"
                                   value="{{ $oldDesc['meta_keywords'] ?? '' }}"
                                   class="form-control">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ============ Right column: post-level metadata ============ --}}
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('admin/blog.cover_image') }}</label>
                @if($isEdit && $blog->image)
                    <div class="mb-2">
                        <img src="{{ route('image.resize', ['size' => 'medium', 'fit' => 'crop', 'path' => $blog->image]) }}"
                             alt="" class="img-thumbnail" style="max-height:140px;">
                    </div>
                @endif
                <input type="file"
                       name="image"
                       accept="image/jpeg,image/png,image/webp"
                       class="form-control-file @error('image') is-invalid @enderror">
                <small class="form-text text-muted">{{ __('admin/blog.cover_image_help') }}</small>
                @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>{{ __('admin/blog.status') }}</label>
                <select name="status" class="form-control">
                    <option value="draft"     @selected(old('status', $blog->status ?? 'draft') === 'draft')>{{ __('admin/blog.status_draft') }}</option>
                    <option value="published" @selected(old('status', $blog->status ?? '') === 'published')>{{ __('admin/blog.status_published') }}</option>
                </select>
            </div>

            <div class="form-group">
                <label>{{ __('admin/blog.published_at') }}</label>
                <input type="datetime-local"
                       name="published_at"
                       value="{{ old('published_at', optional($blog->published_at ?? null)->format('Y-m-d\TH:i')) }}"
                       class="form-control">
                <small class="form-text text-muted">{{ __('admin/blog.published_at_help') }}</small>
            </div>

            <div class="form-group">
                <div class="icheck-primary">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $blog->is_active ?? true))>
                    <label for="is_active">{{ __('admin/blog.is_active') }}</label>
                </div>
            </div>

            <div class="form-group">
                <div class="icheck-primary">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1"
                           @checked(old('is_featured', $blog->is_featured ?? false))>
                    <label for="is_featured">{{ __('admin/blog.is_featured') }}</label>
                </div>
            </div>

            @if($isEdit)
                <hr>
                <p class="small text-muted mb-1">
                    <strong>{{ __('admin/blog.column_views') }}:</strong>
                    {{ number_format((int) $blog->view_count) }}
                </p>
                <p class="small text-muted mb-1">
                    <strong>{{ __('admin/blog.author') }}:</strong>
                    {{ $blog->author?->name ?? '—' }}
                </p>
            @endif
        </div>
    </div>
</div>

<div class="card-footer">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> {{ __('admin/blog.save') }}
    </button>
    <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> {{ __('admin/blog.back') }}
    </a>
</div>

@push('scripts')
<script>
// AI description generator — bound to every .ai-generate-btn on the page.
// Reads sibling inputs (title in the same tab) and POSTs to the backend.
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.ai-generate-btn');
    if (!btn) return;
    e.preventDefault();

    const targetId = btn.dataset.targetId;
    const titleEl  = document.getElementById(btn.dataset.titleId);
    const language = btn.dataset.language || 'tr';
    const ta = document.getElementById(targetId);
    if (!ta) return;

    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>{{ __('admin/ai.generating') }}';

    fetch("{{ route('admin.ai.generate-listing-description') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            language: language,
            title:    titleEl ? titleEl.value : '',
            notes:    ta.value, // Use any existing text as additional notes
        }),
    })
    .then(r => r.json().then(data => ({status: r.status, data})))
    .then(({status, data}) => {
        if (data && data.ok && data.text) {
            // If summernote is initialized on this textarea, push value
            // through it; otherwise set raw textarea value.
            if (window.jQuery && jQuery('#' + targetId).next('.note-editor').length) {
                jQuery('#' + targetId).summernote('code', (data.text || '').replace(/\n/g, '<br>'));
            } else {
                ta.value = data.text;
            }
        } else {
            alert((data && data.error) || '{{ __('admin/ai.request_failed') }}');
        }
    })
    .catch(err => alert('{{ __('admin/ai.request_failed') }}: ' + err.message))
    .finally(() => { btn.disabled = false; btn.innerHTML = original; });
});
</script>
@endpush
