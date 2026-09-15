@extends('admin.layouts.app')

@section('title', 'Yeni İlçe Ekle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Yeni İlçe Ekle</h1>
    <a href="{{ route('admin.locations.districts.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Geri Dön
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">İlçe Bilgileri</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.locations.districts.store') }}" method="POST">
                    @csrf

                    <!-- City Selection -->
                    <div class="form-group mb-3">
                        <label for="city_id">Şehir <span class="text-danger">*</span></label>
                        <select class="form-control @error('city_id') is-invalid @enderror" id="city_id" name="city_id" required>
                            <option value="">Şehir Seçin</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id', $selectedCityId) == $city->id ? 'selected' : '' }}>
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
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">İlçe adı seçilen şehir içinde benzersiz olmalıdır.</small>
                    </div>

                    <!-- Status -->
                    <div class="form-group mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Aktif
                            </label>
                        </div>
                        <small class="form-text text-muted">Pasif ilçeler kullanıcılar tarafından görüntülenemez.</small>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Kaydet
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
                    <i class="fas fa-info-circle me-2"></i>Bilgi
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>İlçe Ekleme:</strong></p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i>Önce şehir seçilmelidir</li>
                    <li><i class="fas fa-check text-success me-2"></i>İlçe adı şehir içinde benzersiz olmalıdır</li>
                    <li><i class="fas fa-check text-success me-2"></i>Slug otomatik oluşturulur</li>
                    <li><i class="fas fa-check text-success me-2"></i>Aktif olmayan ilçeler gizlenir</li>
                </ul>

                <hr>

                <p class="mb-2"><strong>Sonraki Adımlar:</strong></p>
                <p class="text-muted small">İlçe eklendikten sonra bu ilçeye ait mahalleleri ekleyebilirsiniz.</p>
            </div>
        </div>

        @if($selectedCityId)
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>Seçilen Şehir
                </h6>
            </div>
            <div class="card-body">
                @php $selectedCity = $cities->find($selectedCityId) @endphp
                @if($selectedCity)
                    <p class="mb-1"><strong>{{ $selectedCity->name }}</strong></p>
                    <small class="text-muted">Bu şehir için ilçe ekleniyor</small>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
