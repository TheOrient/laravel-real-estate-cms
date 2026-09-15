@extends('admin.layouts.app')

@section('title', 'Yeni Şehir Ekle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Yeni Şehir Ekle</h1>
    <a href="{{ route('admin.locations.cities.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Geri Dön
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Şehir Bilgileri</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.locations.cities.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- City Name -->
                    <div class="form-group mb-3">
                        <label for="name">Şehir Adı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Şehir adı otomatik olarak URL'de kullanılacak hale dönüştürülür.</small>
                    </div>

                    <!-- Status -->
                    <div class="form-group mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Aktif
                            </label>
                        </div>
                        <small class="form-text text-muted">Pasif şehirler kullanıcılar tarafından görüntülenemez.</small>
                    </div>

                    <!-- City Image -->
                    <div class="form-group mb-3">
                        <label for="image">Şehir Görseli</label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror"
                               id="image" name="image" accept="image/*">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Ana sayfadaki "Popüler Şehirler" alanında kullanılacak görsel (önerilen oran: 16:9).
                        </small>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Kaydet
                        </button>
                        <a href="{{ route('admin.locations.cities.index') }}" class="btn btn-secondary">
                            İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Bilgi
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Şehir Ekleme:</strong></p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i>Şehir adı benzersiz olmalıdır</li>
                    <li><i class="fas fa-check text-success me-2"></i>Slug otomatik oluşturulur</li>
                    <li><i class="fas fa-check text-success me-2"></i>Aktif olmayan şehirler gizlenir</li>
                </ul>

                <hr>

                <p class="mb-2"><strong>Sonraki Adımlar:</strong></p>
                <p class="text-muted small">Şehir eklendikten sonra bu şehre ait ilçeleri ekleyebilirsiniz.</p>
            </div>
        </div>
    </div>
</div>
@endsection
