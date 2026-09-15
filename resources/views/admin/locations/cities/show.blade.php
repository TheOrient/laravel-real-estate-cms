@extends('admin.layouts.app')

@section('title', 'Şehir: ' . $city->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Şehir: {{ $city->name }}</h1>
    <div class="btn-group">
        <a href="{{ route('admin.locations.cities.edit', $city) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Düzenle
        </a>
        <a href="{{ route('admin.locations.cities.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Geri Dön
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">İlçe Sayısı</h6>
                        <h2 class="mb-0">{{ $city->districts->count() }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-map fa-2x"></i>
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
                        <h6 class="card-title">Mahalle Sayısı</h6>
                        <h2 class="mb-0">{{ $city->districts->sum(function($district) { return $district->neighborhoods->count(); }) }}</h2>
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
                        <h6 class="card-title">İlan Sayısı</h6>
                        <h2 class="mb-0">{{ $city->listings->count() }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-list fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-{{ $city->is_active ? 'primary' : 'danger' }} text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Durum</h6>
                        <h5 class="mb-0">{{ $city->is_active ? 'Aktif' : 'Pasif' }}</h5>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-{{ $city->is_active ? 'check-circle' : 'times-circle' }} fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- City Details -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Şehir Detayları
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>ID:</strong></td>
                        <td>{{ $city->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Adı:</strong></td>
                        <td>{{ $city->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Slug:</strong></td>
                        <td><code>{{ $city->slug }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Durum:</strong></td>
                        <td>
                            @if($city->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Pasif</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Oluşturulma:</strong></td>
                        <td>{{ $city->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Son Güncelleme:</strong></td>
                        <td>{{ $city->updated_at->format('d.m.Y H:i') }}</td>
                    </tr>
                </table>

                <!-- Quick Actions -->
                <div class="mt-3">
                    <h6>Hızlı İşlemler:</h6>
                    <a href="{{ route('admin.locations.districts.create', ['city_id' => $city->id]) }}" class="btn btn-sm btn-primary mb-2 d-block">
                        <i class="fas fa-plus me-2"></i>İlçe Ekle
                    </a>
                    <a href="{{ route('admin.locations.districts.index', ['city_id' => $city->id]) }}" class="btn btn-sm btn-info mb-2 d-block">
                        <i class="fas fa-list me-2"></i>İlçeleri Görüntüle
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Districts List -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-map me-2"></i>İlçeler ({{ $city->districts->count() }})
                </h5>
                <a href="{{ route('admin.locations.districts.create', ['city_id' => $city->id]) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-2"></i>İlçe Ekle
                </a>
            </div>
            <div class="card-body">
                @if($city->districts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>İlçe Adı</th>
                                    <th>Mahalle Sayısı</th>
                                    <th>İlan Sayısı</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($city->districts as $district)
                                <tr>
                                    <td>
                                        <strong>{{ $district->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $district->neighborhoods->count() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $district->listings->count() }}</span>
                                    </td>
                                    <td>
                                        @if($district->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Pasif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.locations.districts.show', $district) }}" class="btn btn-info" title="Görüntüle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.locations.districts.edit', $district) }}" class="btn btn-warning" title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-map fa-3x text-muted mb-3"></i>
                        <h5>Bu şehirde henüz ilçe bulunmuyor</h5>
                        <p class="text-muted">İlk ilçeyi eklemek için yukarıdaki butonu kullanın.</p>
                        <a href="{{ route('admin.locations.districts.create', ['city_id' => $city->id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>İlk İlçeyi Ekle
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
