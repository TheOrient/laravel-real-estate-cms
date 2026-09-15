<div class="card-body">
    <div class="row">
        <div class="col-md-8">
            <!-- Language Tabs -->
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
                                <span class="badge badge-primary ml-1">{{ __('admin/general.default') }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="tab-content border-left border-right border-bottom p-3" id="languageTabContent">
                @foreach($languages as $index => $language)
                    @php
                        $oldDesc = old("descriptions.{$language->id}", $descriptions[$language->id] ?? []);
                    @endphp
                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                         id="lang-{{ $language->id }}"
                         role="tabpanel"
                         aria-labelledby="lang-{{ $language->id }}-tab">
                        <div class="form-group">
                            <label for="title_{{ $language->id }}">{{ __('admin/pages.page_title') }} @if($language->is_default)<span class="text-danger">*</span>@endif</label>
                            <input type="text"
                                   required
                                   class="form-control @error("descriptions.{$language->id}.title") is-invalid @enderror"
                                   id="title_{{ $language->id }}"
                                   name="descriptions[{{ $language->id }}][title]"
                                   value="{{ $oldDesc['title'] ?? '' }}"
                                   {{ $language->is_default ? 'required' : '' }}>
                            @error("descriptions.{$language->id}.title")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="slug_{{ $language->id }}">{{ __('admin/pages.slug') }}</label>
                            <input type="text"
                                   required
                                   class="form-control @error("descriptions.{$language->id}.slug") is-invalid @enderror"
                                   id="slug_{{ $language->id }}"
                                   name="descriptions[{{ $language->id }}][slug]"
                                   value="{{ $oldDesc['slug'] ?? '' }}"
                                   placeholder="{{ __('admin/pages.slug_placeholder') }}">
                            @error("descriptions.{$language->id}.slug")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="meta_description_{{ $language->id }}">{{ __('admin/pages.meta_description') }}</label>
                            <textarea class="form-control @error("descriptions.{$language->id}.meta_description") is-invalid @enderror"
                                      id="meta_description_{{ $language->id }}"
                                      name="descriptions[{{ $language->id }}][meta_description]"
                                      required
                                      rows="3"
                                      placeholder="{{ __('admin/pages.description_placeholder') }}">{{ $oldDesc['meta_description'] ?? '' }}</textarea>
                            @error("descriptions.{$language->id}.meta_description")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="content_{{ $language->id }}">{{ __('admin/pages.content') }}</label>
                            <textarea class="form-control summernote @error("descriptions.{$language->id}.content") is-invalid @enderror"
                                      id="content_{{ $language->id }}"
                                      name="descriptions[{{ $language->id }}][content]"
                                      required
                                      rows="8"
                                      placeholder="{{ __('admin/pages.content_placeholder') }}">{{ $oldDesc['content'] ?? '' }}</textarea>
                            @error("descriptions.{$language->id}.content")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-md-4">
            <!-- Status -->
            <div class="form-group">
                <div class="icheck-primary">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }}>
                    <label for="is_active">{{ __('admin/pages.is_active') }}</label>
                </div>
            </div>

            <!-- Show Header -->
            <div class="form-group">
                <div class="icheck-primary">
                    <input type="checkbox" id="show_header" name="show_header" value="1"
                           {{ old('show_header', $page->show_header ?? true) ? 'checked' : '' }}>
                    <label for="show_header">Header Göster</label>
                </div>
                <small class="form-text text-muted">Sayfada üst menü (header) gösterilsin mi?</small>
            </div>

            <!-- Show Footer -->
            <div class="form-group">
                <div class="icheck-primary">
                    <input type="checkbox" id="show_footer" name="show_footer" value="1"
                           {{ old('show_footer', $page->show_footer ?? true) ? 'checked' : '' }}>
                    <label for="show_footer">Footer Göster</label>
                </div>
                <small class="form-text text-muted">Sayfada alt bilgi (footer) gösterilsin mi?</small>
            </div>

            <!-- Sort Order -->
            <div class="form-group">
                <label for="sort_order">{{ __('admin/pages.sort_order') }}</label>
                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                       id="sort_order" name="sort_order" min="0"
                       value="{{ old('sort_order', $page->sort_order ?? 0) }}"
                       placeholder="{{ __('admin/pages.sort_order_placeholder') }}">
                @error('sort_order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if(isset($page) && $page->exists)
                <!-- Creation Info -->
                <div class="form-group">
                    <label>{{ __('admin/pages.created_at') }}</label>
                    <p class="form-control-plaintext">{{ $page->created_at->format('d.m.Y H:i') }}</p>
                </div>

                <!-- Update Info -->
                <div class="form-group">
                    <label>{{ __('admin/pages.updated_at') }}</label>
                    <p class="form-control-plaintext">{{ $page->updated_at->format('d.m.Y H:i') }}</p>
                </div>

                <!-- Page URL -->
                @php
                    $defaultDescription = $page->descriptions()->whereHas('language', function($q) { $q->where('is_default', true); })->first();
                @endphp
                @if($defaultDescription)
                    <div class="form-group">
                        <label>{{ __('admin/general.url') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ route('pages.show', $defaultDescription->slug) }}" readonly>
                            <div class="input-group-append">
                                <a href="{{ route('pages.show', $defaultDescription->slug) }}" class="btn btn-info" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<div class="card-footer">
    <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> {{ __('admin/pages.save') }}
            </button>
            <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> {{ __('admin/pages.back') }}
            </a>
            @if(isset($page) && $page->exists)
                <a href="{{ route('pages.show', $page->slug) }}" class="btn btn-info" target="_blank">
                    <i class="fas fa-eye mr-1"></i> {{ __('admin/pages.view_page') }}
                </a>
            @endif
        </div>
    </div>
</div>
