@extends('admin.layouts.app')

@section('title', 'İlçe Düzenle: ' . $district->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">İlçe Düzenle: {{ $district->name }}</h1>
    <div class="btn-group">
        <a href="{{ route('admin.locations.districts.show', $district) }}" class="btn btn-info">
            <i class="fas fa-eye me-2"></i>Görüntüle
        </a>
        <a href="{{ route('admin.locations.districts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Geri Dön
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">İlçe Bilgileri</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.locations.districts.update', $district) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- City Selection -->
                    <div class="form-group mb-3">
                        <label for="city_id">Şehir <span class="text-danger">*</span></label>
                        <select class="form-control @error('city_id') is-invalid @enderror" id="city_id" name="city_id" required>
                            <option value="">Şehir Seçin</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id', $district->city_id) == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('city_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- District Name -->
                    <div class="form-group mb-3">
                        <label for="name">İlçe Adı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $district->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">İlçe adı değiştirildiğinde slug otomatik olarak güncellenir.</small>
                    </div>

                    <!-- Status -->
                    <div class="form-group mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $district->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Aktif
                            </label>
                        </div>
                        <small class="form-text text-muted">Pasif ilçeler kullanıcılar tarafından görüntülenemez.</small>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Güncelle
                        </button>
                        <a href="{{ route('admin.locations.districts.index') }}" class="btn btn-secondary">
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
                    <i class="fas fa-info-circle me-2"></i>İlçe Bilgileri
                </h5>
            </div>
            <div class="card-body">
                <p><strong>Şehir:</strong> {{ $district->city->name }}</p>
                <p><strong>Slug:</strong> <code>{{ $district->slug }}</code></p>
                <p><strong>Oluşturulma:</strong> {{ $district->created_at->format('d.m.Y H:i') }}</p>
                <p><strong>Son Güncelleme:</strong> {{ $district->updated_at->format('d.m.Y H:i') }}</p>

                <hr>

                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $district->neighborhoods()->count() }}</h4>
                        <small class="text-muted">Mahalle</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $district->listings()->count() }}</h4>
                        <small class="text-muted">İlan</small>
                    </div>
                </div>

                @if($district->neighborhoods()->count() > 0)
                <hr>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Dikkat:</strong> Bu ilçede mahalleler bulunmaktadır. İlçe silinmeden önce mahallelerin silinmesi gerekir.
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Hızlı İşlemler
                </h6>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.locations.neighborhoods.create', ['district_id' => $district->id]) }}" class="btn btn-sm btn-primary mb-2 d-block">
                    <i class="fas fa-plus me-2"></i>Mahalle Ekle
                </a>
                <a href="{{ route('admin.locations.neighborhoods.index', ['district_id' => $district->id]) }}" class="btn btn-sm btn-info mb-2 d-block">
                    <i class="fas fa-list me-2"></i>Mahalleleri Görüntüle
                </a>
                <a href="{{ route('admin.locations.cities.show', $district->city) }}" class="btn btn-sm btn-secondary mb-2 d-block">
                    <i class="fas fa-city me-2"></i>Şehri Görüntüle
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
