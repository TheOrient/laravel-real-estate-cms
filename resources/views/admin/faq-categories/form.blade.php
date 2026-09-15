<div class="card-body">
    <div class="row">
        <div class="col-md-8">
            <!-- Name -->
            <div class="form-group">
                <label for="name">Kategori Adı <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       id="name" name="name"
                       value="{{ old('name', $faqCategory->name ?? '') }}"
                       placeholder="Örn: Genel, Alım-Satım" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Slug -->
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                       id="slug" name="slug"
                       value="{{ old('slug', $faqCategory->slug ?? '') }}"
                       placeholder="Otomatik oluşturulur">
                @error('slug')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Boş bırakılırsa kategori adından otomatik oluşturulur</small>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Status -->
            <div class="form-group">
                <div class="icheck-primary">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           {{ old('is_active', isset($faqCategory) ? $faqCategory->is_active : true) ? 'checked' : '' }}>
                    <label for="is_active">Aktif</label>
                </div>
            </div>

            <!-- Sort Order -->
            <div class="form-group">
                <label for="sort_order">Sıralama</label>
                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                       id="sort_order" name="sort_order" min="0"
                       value="{{ old('sort_order', $faqCategory->sort_order ?? 0) }}"
                       placeholder="0">
                @error('sort_order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Düşük sayı önce gösterilir</small>
            </div>

            @if(isset($faqCategory) && $faqCategory->exists)
                <!-- Creation Info -->
                <div class="form-group">
                    <label>Oluşturulma</label>
                    <p class="form-control-plaintext">{{ $faqCategory->created_at->format('d.m.Y H:i') }}</p>
                </div>

                <!-- Update Info -->
                <div class="form-group">
                    <label>Güncellenme</label>
                    <p class="form-control-plaintext">{{ $faqCategory->updated_at->format('d.m.Y H:i') }}</p>
                </div>

                <!-- FAQ Count -->
                <div class="form-group">
                    <label>FAQ Sayısı</label>
                    <p class="form-control-plaintext">
                        <span class="badge badge-info">{{ $faqCategory->faqs->count() }}</span>
                    </p>
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
            <a href="{{ route('admin.faq-categories.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Geri Dön
            </a>
        </div>
    </div>
</div>
