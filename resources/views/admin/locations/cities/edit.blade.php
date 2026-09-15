@extends('admin.layouts.app')

@section('title', 'Şehir Düzenle: ' . $city->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Şehir Düzenle: {{ $city->name }}</h1>
    <div class="btn-group">
        <a href="{{ route('admin.locations.cities.show', $city) }}" class="btn btn-info">
            <i class="fas fa-eye me-2"></i>Görüntüle
        </a>
        <a href="{{ route('admin.locations.cities.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Geri Dön
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Şehir Bilgileri</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.locations.cities.update', $city) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- City Name -->
                    <div class="form-group mb-3">
                        <label for="name">Şehir Adı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $city->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Şehir adı değiştirildiğinde slug otomatik olarak güncellenir.</small>
                    </div>

                    <!-- Status -->
                    <div class="form-group mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $city->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Aktif
                            </label>
                        </div>
                        <small class="form-text text-muted">Pasif şehirler kullanıcılar tarafından görüntülenemez.</small>
                    </div>

                    <!-- City Image -->
                    <div class="form-group mb-3">
                        <label for="image">Şehir Görseli</label>
                        @if($city->image)
                            <div class="mb-2">
                                <img src="{{ asset($city->image) }}" alt="{{ $city->name }}" class="img-fluid rounded" style="max-height: 160px;">
                            </div>
                        @endif
                        <input type="file" class="form-control @error('image') is-invalid @enderror"
                               id="image" name="image" accept="image/*">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Ana sayfadaki \"Popüler Şehirler\" alanında kullanılacak görseli güncelleyebilirsiniz.
                        </small>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Güncelle
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
                    <i class="fas fa-info-circle me-2"></i>Şehir Bilgileri
                </h5>
            </div>
            <div class="card-body">
                <p><strong>Slug:</strong> <code>{{ $city->slug }}</code></p>
                <p><strong>Oluşturulma:</strong> {{ $city->created_at->format('d.m.Y H:i') }}</p>
                <p><strong>Son Güncelleme:</strong> {{ $city->updated_at->format('d.m.Y H:i') }}</p>

                <hr>

                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $city->districts()->count() }}</h4>
                        <small class="text-muted">İlçe</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $city->listings()->count() }}</h4>
                        <small class="text-muted">İlan</small>
                    </div>
                </div>

                @if($city->districts()->count() > 0)
                <hr>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Dikkat:</strong> Bu şehirde ilçeler bulunmaktadır. Şehir silinmeden önce ilçelerin silinmesi gerekir.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
