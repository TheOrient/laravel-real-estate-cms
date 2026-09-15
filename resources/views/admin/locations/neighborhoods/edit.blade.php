@extends('admin.layouts.app')

@section('title', 'Mahalle Düzenle: ' . $neighborhood->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Mahalle Düzenle: {{ $neighborhood->name }}</h1>
    <div class="btn-group">
        <a href="{{ route('admin.locations.neighborhoods.show', $neighborhood) }}" class="btn btn-info">
            <i class="fas fa-eye me-2"></i>Görüntüle
        </a>
        <a href="{{ route('admin.locations.neighborhoods.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Geri Dön
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Mahalle Bilgileri</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.locations.neighborhoods.update', $neighborhood) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- City Selection -->
                    <div class="form-group mb-3">
                        <label for="city_id">Şehir <span class="text-danger">*</span></label>
                        <select class="form-control @error('city_id') is-invalid @enderror" id="city_id" name="city_id">
                            <option value="">Şehir Seçin</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id', $neighborhood->district->city_id) == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('city_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- District Selection -->
                    <div class="form-group mb-3">
                        <label for="district_id">İlçe <span class="text-danger">*</span></label>
                        <select class="form-control @error('district_id') is-invalid @enderror" id="district_id" name="district_id" required>
                            <option value="">İlçe Seçin</option>
                            @foreach($districts as $district)
                                <option value="{{ $district->id }}"
                                        data-city-id="{{ $district->city_id }}"
                                        {{ old('district_id', $neighborhood->district_id) == $district->id ? 'selected' : '' }}>
                                    {{ $district->name }} ({{ $district->city->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('district_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Neighborhood Name -->
                    <div class="form-group mb-3">
                        <label for="name">Mahalle Adı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $neighborhood->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Mahalle adı değiştirildiğinde slug otomatik olarak güncellenir.</small>
                    </div>

                    <!-- Status -->
                    <div class="form-group mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $neighborhood->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Aktif
                            </label>
                        </div>
                        <small class="form-text text-muted">Pasif mahalleler kullanıcılar tarafından görüntülenemez.</small>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Güncelle
                        </button>
                        <a href="{{ route('admin.locations.neighborhoods.index') }}" class="btn btn-secondary">
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
                    <i class="fas fa-info-circle me-2"></i>Mahalle Bilgileri
                </h5>
            </div>
            <div class="card-body">
                <p><strong>Şehir:</strong> {{ $neighborhood->district->city->name }}</p>
                <p><strong>İlçe:</strong> {{ $neighborhood->district->name }}</p>
                <p><strong>Slug:</strong> <code>{{ $neighborhood->slug }}</code></p>
                <p><strong>Oluşturulma:</strong> {{ $neighborhood->created_at->format('d.m.Y H:i') }}</p>
                <p><strong>Son Güncelleme:</strong> {{ $neighborhood->updated_at->format('d.m.Y H:i') }}</p>

                <hr>

                <div class="text-center">
                    <h4 class="text-success">{{ $neighborhood->listings()->count() }}</h4>
                    <small class="text-muted">İlan Sayısı</small>
                </div>

                @if($neighborhood->listings()->count() > 0)
                <hr>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Dikkat:</strong> Bu mahallede ilanlar bulunmaktadır. Mahalle silinmeden önce ilanların silinmesi gerekir.
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
                <a href="{{ route('admin.locations.districts.show', $neighborhood->district) }}" class="btn btn-sm btn-info mb-2 d-block">
                    <i class="fas fa-map me-2"></i>İlçeyi Görüntüle
                </a>
                <a href="{{ route('admin.locations.cities.show', $neighborhood->district->city) }}" class="btn btn-sm btn-secondary mb-2 d-block">
                    <i class="fas fa-city me-2"></i>Şehri Görüntüle
                </a>
                <a href="{{ route('admin.locations.neighborhoods.index', ['district_id' => $neighborhood->district_id]) }}" class="btn btn-sm btn-warning mb-2 d-block">
                    <i class="fas fa-list me-2"></i>Aynı İlçedeki Mahalleler
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Dynamic district filtering based on city selection
document.getElementById('city_id').addEventListener('change', function() {
    const cityId = this.value;
    const districtSelect = document.getElementById('district_id');
    const districtOptions = districtSelect.querySelectorAll('option');

    // Reset district selection if city changed
    const currentDistrictCityId = districtSelect.options[districtSelect.selectedIndex]?.getAttribute('data-city-id');
    if (currentDistrictCityId !== cityId) {
        districtSelect.value = '';
    }

    // Show/hide districts based on city selection
    districtOptions.forEach(option => {
        if (option.value === '') {
            option.style.display = 'block'; // Always show "İlçe Seçin" option
        } else {
            const optionCityId = option.getAttribute('data-city-id');
            option.style.display = (!cityId || optionCityId === cityId) ? 'block' : 'none';
        }
    });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const citySelect = document.getElementById('city_id');
    if (citySelect.value) {
        citySelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
