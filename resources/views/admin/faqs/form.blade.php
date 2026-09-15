<div class="card-body">
    <div class="row">
        <div class="col-md-8">
            <!-- Category -->
            <div class="form-group">
                <label for="faq_category_id">Kategori <span class="text-danger">*</span></label>
                <select class="form-control @error('faq_category_id') is-invalid @enderror"
                        id="faq_category_id" name="faq_category_id" required>
                    <option value="">Kategori Seçin</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                                {{ old('faq_category_id', isset($faq) ? $faq->faq_category_id : '') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('faq_category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Question -->
            <div class="form-group">
                <label for="question">Soru <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('question') is-invalid @enderror"
                       id="question" name="question"
                       value="{{ old('question', $faq->question ?? '') }}"
                       placeholder="FAQ sorusu" required maxlength="500">
                @error('question')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Answer -->
            <div class="form-group">
                <label for="answer">Cevap <span class="text-danger">*</span></label>
                <textarea class="form-control @error('answer') is-invalid @enderror"
                          id="answer" name="answer" rows="8"
                          placeholder="FAQ cevabı" required>{{ old('answer', $faq->answer ?? '') }}</textarea>
                @error('answer')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-4">
            <!-- Status -->
            <div class="form-group">
                <div class="icheck-primary">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           {{ old('is_active', isset($faq) ? $faq->is_active : true) ? 'checked' : '' }}>
                    <label for="is_active">Aktif</label>
                </div>
            </div>

            <!-- Sort Order -->
            <div class="form-group">
                <label for="sort_order">Sıralama</label>
                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                       id="sort_order" name="sort_order" min="0"
                       value="{{ old('sort_order', $faq->sort_order ?? 0) }}"
                       placeholder="0">
                @error('sort_order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Düşük sayı önce gösterilir</small>
            </div>

            @if(isset($faq) && $faq->exists)
                <!-- Creation Info -->
                <div class="form-group">
                    <label>Oluşturulma</label>
                    <p class="form-control-plaintext">{{ $faq->created_at->format('d.m.Y H:i') }}</p>
                </div>

                <!-- Update Info -->
                <div class="form-group">
                    <label>Güncellenme</label>
                    <p class="form-control-plaintext">{{ $faq->updated_at->format('d.m.Y H:i') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="card-footer">
    <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Kaydet
            </button>
            <a href="{{ route('admin.faqs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Geri Dön
            </a>
        </div>
    </div>
</div>
