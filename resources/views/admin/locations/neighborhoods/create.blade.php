@extends('admin.layouts.app')

@section('title', 'Yeni Mahalle Ekle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Yeni Mahalle Ekle</h1>
    <a href="{{ route('admin.locations.neighborhoods.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Geri Dön
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Mahalle Bilgileri</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.locations.neighborhoods.store') }}" method="POST">
                    @csrf

                    <!-- City Selection -->
                    <div class="form-group mb-3">
                        <label for="city_id">Şehir <span class="text-danger">*</span></label>
                        <select class="form-control @error('city_id') is-invalid @enderror" id="city_id" name="city_id">
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

                    <!-- District Selection -->
                    <div class="form-group mb-3">
                        <label for="district_id">İlçe <span class="text-danger">*</span></label>
                        <select class="form-control @error('district_id') is-invalid @enderror" id="district_id" name="district_id" required>
                            <option value="">İlçe Seçin</option>
                            @foreach($districts as $district)
                                <option value="{{ $district->id }}"
                                        data-city-id="{{ $district->city_id }}"
                                        {{ old('district_id', $selectedDistrictId) == $district->id ? 'selected' : '' }}
                                        style="{{ $selectedCityId && $district->city_id != $selectedCityId ? 'display: none;' : '' }}">
                                    {{ $district->name }}
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
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Mahalle adı seçilen ilçe içinde benzersiz olmalıdır.</small>
                    </div>

                    <!-- Status -->
                    <div class="form-group mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Aktif
                            </label>
                        </div>
                        <small class="form-text text-muted">Pasif mahalleler kullanıcılar tarafından görüntülenemez.</small>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Kaydet
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
                    <i class="fas fa-info-circle me-2"></i>Bilgi
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Mahalle Ekleme:</strong></p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i>Önce şehir ve ilçe seçilmelidir</li>
                    <li><i class="fas fa-check text-success me-2"></i>Mahalle adı ilçe içinde benzersiz olmalıdır</li>
                    <li><i class="fas fa-check text-success me-2"></i>Slug otomatik oluşturulur</li>
                    <li><i class="fas fa-check text-success me-2"></i>Aktif olmayan mahalleler gizlenir</li>
                </ul>

                <hr>

                <p class="mb-2"><strong>Dikkat:</strong></p>
                <p class="text-muted small">Şehir değiştirildiğinde ilçe listesi otomatik olarak güncellenir.</p>
            </div>
        </div>

        @if($selectedDistrictId)
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>Seçilen Konum
                </h6>
            </div>
            <div class="card-body">
                @php
                    $selectedDistrict = $districts->find($selectedDistrictId);
                @endphp
                @if($selectedDistrict)
                    <p class="mb-1"><strong>{{ $selectedDistrict->city->name }}</strong> / <strong>{{ $selectedDistrict->name }}</strong></p>
                    <small class="text-muted">Bu ilçe için mahalle ekleniyor</small>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Dynamic district filtering based on city selection
document.getElementById('city_id').addEventListener('change', function() {
    const cityId = this.value;
    const districtSelect = document.getElementById('district_id');
    const districtOptions = districtSelect.querySelectorAll('option');

    // Reset district selection
    districtSelect.value = '';

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
