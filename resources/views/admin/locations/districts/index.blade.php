@extends('admin.layouts.app')

@section('title', 'İlçe Yönetimi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">İlçe Yönetimi</h1>
    <a href="{{ route('admin.locations.districts.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Yeni İlçe Ekle
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
        <form method="GET" action="{{ route('admin.locations.districts.index') }}">
            <div class="row">
                <div class="col-md-6">
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
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-info me-2">
                        <i class="fas fa-search me-2"></i>Filtrele
                    </button>
                    <a href="{{ route('admin.locations.districts.index') }}" class="btn btn-secondary">
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
                        <h6 class="card-title">Toplam İlçe</h6>
                        <h2 class="mb-0">{{ $districts->total() }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-map fa-2x"></i>
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
                        <h6 class="card-title">Aktif İlçe</h6>
                        <h2 class="mb-0">{{ $districts->where('is_active', 1)->count() }}</h2>
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
                        <h6 class="card-title">Toplam Mahalle</h6>
                        <h2 class="mb-0">{{ $districts->sum('neighborhoods_count') }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-home fa-2x"></i>
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
                        <h6 class="card-title">Toplam İlan</h6>
                        <h2 class="mb-0">{{ $districts->sum('listings_count') }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-list fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Districts Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">İlçeler
            @if(request('city_id'))
                - {{ $cities->find(request('city_id'))->name }}
            @endif
        </h5>
    </div>
    <div class="card-body">
        @if($districts->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>İlçe Adı</th>
                            <th>Şehir</th>
                            <th>Slug</th>
                            <th>Mahalle Sayısı</th>
                            <th>İlan Sayısı</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($districts as $district)
                        <tr>
                            <td>{{ $district->id }}</td>
                            <td>
                                <strong>{{ $district->name }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('admin.locations.cities.show', $district->city) }}" class="text-decoration-none">
                                    {{ $district->city->name }}
                                </a>
                            </td>
                            <td>
                                <code>{{ $district->slug }}</code>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $district->neighborhoods_count }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $district->listings_count }}</span>
                            </td>
                            <td>
                                @if($district->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Pasif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.locations.districts.show', $district) }}" class="btn btn-sm btn-info" title="Görüntüle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.locations.districts.edit', $district) }}" class="btn btn-sm btn-warning" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.locations.districts.destroy', $district) }}" method="POST" style="display: inline;" onsubmit="return confirm('Bu ilçeyi silmek istediğinizden emin misiniz?')">
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
                {{ $districts->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-map fa-3x text-muted mb-3"></i>
                @if(request('city_id'))
                    <h5>{{ $cities->find(request('city_id'))->name }} şehrinde henüz ilçe eklenmemiş</h5>
                    <p class="text-muted">Bu şehir için ilk ilçeyi eklemek için aşağıdaki butonu kullanın.</p>
                    <a href="{{ route('admin.locations.districts.create', ['city_id' => request('city_id')]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>İlk İlçeyi Ekle
                    </a>
                @else
                    <h5>Henüz ilçe eklenmemiş</h5>
                    <p class="text-muted">İlk ilçeyi eklemek için yukarıdaki butonu kullanın.</p>
                    <a href="{{ route('admin.locations.districts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>İlk İlçeyi Ekle
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
