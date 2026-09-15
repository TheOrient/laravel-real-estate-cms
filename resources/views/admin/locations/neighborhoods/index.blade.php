@extends('admin.layouts.app')

@section('title', 'Mahalle Yönetimi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Mahalle Yönetimi</h1>
    <a href="{{ route('admin.locations.neighborhoods.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Yeni Mahalle Ekle
    </a>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>Filtrele
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.locations.neighborhoods.index') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="city_id">Şehir Seçin</label>
                        <select class="form-control" id="city_id" name="city_id">
                            <option value="">Tüm Şehirler</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="district_id">İlçe Seçin</label>
                        <select class="form-control" id="district_id" name="district_id">
                            <option value="">Tüm İlçeler</option>
                            @foreach($districts as $district)
                                <option value="{{ $district->id }}" {{ request('district_id') == $district->id ? 'selected' : '' }}>
                                    {{ $district->name }} ({{ $district->city->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-info me-2">
                        <i class="fas fa-search me-2"></i>Filtrele
                    </button>
                    <a href="{{ route('admin.locations.neighborhoods.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Temizle
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Toplam Mahalle</h6>
                        <h2 class="mb-0">{{ $neighborhoods->total() }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-home fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Aktif Mahalle</h6>
                        <h2 class="mb-0">{{ $neighborhoods->where('is_active', 1)->count() }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Toplam İlan</h6>
                        <h2 class="mb-0">{{ $neighborhoods->sum('listings_count') }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-list fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Farklı İlçe</h6>
                        <h2 class="mb-0">{{ $neighborhoods->pluck('district.city.name')->unique()->count() }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-map fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Neighborhoods Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Mahalleler
            @if(request('city_id') && request('district_id'))
                @php
                    $selectedCity = $cities->find(request('city_id'));
                    $selectedDistrict = $districts->find(request('district_id'));
                @endphp
                @if($selectedCity && $selectedDistrict)
                    - {{ $selectedDistrict->name }}, {{ $selectedCity->name }}
                @endif
            @elseif(request('city_id'))
                @php $selectedCity = $cities->find(request('city_id')); @endphp
                @if($selectedCity)
                    - {{ $selectedCity->name }}
                @endif
            @elseif(request('district_id'))
                @php $selectedDistrict = $districts->find(request('district_id')); @endphp
                @if($selectedDistrict)
                    - {{ $selectedDistrict->name }}
                @endif
            @endif
        </h5>
    </div>
    <div class="card-body">
        @if($neighborhoods->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mahalle Adı</th>
                            <th>İlçe</th>
                            <th>Şehir</th>
                            <th>Slug</th>
                            <th>İlan Sayısı</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($neighborhoods as $neighborhood)
                        <tr>
                            <td>{{ $neighborhood->id }}</td>
                            <td>
                                <strong>{{ $neighborhood->name }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('admin.locations.districts.show', $neighborhood->district) }}" class="text-decoration-none">
                                    {{ $neighborhood->district->name }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.locations.cities.show', $neighborhood->district->city) }}" class="text-decoration-none">
                                    {{ $neighborhood->district->city->name }}
                                </a>
                            </td>
                            <td>
                                <code>{{ $neighborhood->slug }}</code>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $neighborhood->listings_count }}</span>
                            </td>
                            <td>
                                @if($neighborhood->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Pasif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.locations.neighborhoods.show', $neighborhood) }}" class="btn btn-sm btn-info" title="Görüntüle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.locations.neighborhoods.edit', $neighborhood) }}" class="btn btn-sm btn-warning" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.locations.neighborhoods.destroy', $neighborhood) }}" method="POST" style="display: inline;" onsubmit="return confirm('Bu mahalleyi silmek istediğinizden emin misiniz?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $neighborhoods->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-home fa-3x text-muted mb-3"></i>
                @if(request('district_id'))
                    @php $selectedDistrict = $districts->find(request('district_id')); @endphp
                    @if($selectedDistrict)
                        <h5>{{ $selectedDistrict->name }} ilçesinde henüz mahalle eklenmemiş</h5>
                        <p class="text-muted">Bu ilçe için ilk mahalleyi eklemek için aşağıdaki butonu kullanın.</p>
                        <a href="{{ route('admin.locations.neighborhoods.create', ['district_id' => request('district_id')]) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>İlk Mahalleyi Ekle
                        </a>
                    @endif
                @else
                    <h5>Henüz mahalle eklenmemiş</h5>
                    <p class="text-muted">İlk mahalleyi eklemek için yukarıdaki butonu kullanın.</p>
                    <a href="{{ route('admin.locations.neighborhoods.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>İlk Mahalleyi Ekle
                    </a>
                @endif
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

    // Clear district options
    districtSelect.innerHTML = '<option value="">Tüm İlçeler</option>';

    if (cityId) {
        // Fetch districts for selected city
        fetch(`{{ url('admin/locations/cities') }}/${cityId}/districts`)
            .then(response => response.json())
            .then(districts => {
                districts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.id;
                    option.textContent = district.name;
                    districtSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    }
});
</script>
@endpush
@endsection
