@extends('admin.layouts.app')

@section('title', 'İlçe: ' . $district->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">İlçe: {{ $district->name }} <small class="text-muted">/ {{ $district->city->name }}</small></h1>
    <div class="btn-group">
        <a href="{{ route('admin.locations.districts.edit', $district) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Düzenle
        </a>
        <a href="{{ route('admin.locations.districts.index') }}" class="btn btn-secondary">
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
                        <h6 class="card-title">Mahalle Sayısı</h6>
                        <h2 class="mb-0">{{ $district->neighborhoods->count() }}</h2>
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
                        <h2 class="mb-0">{{ $district->listings->count() }}</h2>
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
                        <h6 class="card-title">Aktif Mahalle</h6>
                        <h2 class="mb-0">{{ $district->neighborhoods->where('is_active', true)->count() }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-{{ $district->is_active ? 'primary' : 'danger' }} text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Durum</h6>
                        <h5 class="mb-0">{{ $district->is_active ? 'Aktif' : 'Pasif' }}</h5>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-{{ $district->is_active ? 'check-circle' : 'times-circle' }} fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- District Details -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>İlçe Detayları
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>ID:</strong></td>
                        <td>{{ $district->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Adı:</strong></td>
                        <td>{{ $district->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Şehir:</strong></td>
                        <td>
                            <a href="{{ route('admin.locations.cities.show', $district->city) }}" class="text-decoration-none">
                                {{ $district->city->name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Slug:</strong></td>
                        <td><code>{{ $district->slug }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Durum:</strong></td>
                        <td>
                            @if($district->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Pasif</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Oluşturulma:</strong></td>
                        <td>{{ $district->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Son Güncelleme:</strong></td>
                        <td>{{ $district->updated_at->format('d.m.Y H:i') }}</td>
                    </tr>
                </table>

                <!-- Quick Actions -->
                <div class="mt-3">
                    <h6>Hızlı İşlemler:</h6>
                    <a href="{{ route('admin.locations.neighborhoods.create', ['district_id' => $district->id]) }}" class="btn btn-sm btn-primary mb-2 d-block">
                        <i class="fas fa-plus me-2"></i>Mahalle Ekle
                    </a>
                    <a href="{{ route('admin.locations.neighborhoods.index', ['district_id' => $district->id]) }}" class="btn btn-sm btn-info mb-2 d-block">
                        <i class="fas fa-list me-2"></i>Mahalleleri Görüntüle
                    </a>
                    <a href="{{ route('admin.locations.cities.show', $district->city) }}" class="btn btn-sm btn-secondary mb-2 d-block">
                        <i class="fas fa-city me-2"></i>{{ $district->city->name }} Şehrini Görüntüle
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Neighborhoods List -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-home me-2"></i>Mahalleler ({{ $district->neighborhoods->count() }})
                </h5>
                <a href="{{ route('admin.locations.neighborhoods.create', ['district_id' => $district->id]) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-2"></i>Mahalle Ekle
                </a>
            </div>
            <div class="card-body">
                @if($district->neighborhoods->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Mahalle Adı</th>
                                    <th>İlan Sayısı</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($district->neighborhoods as $neighborhood)
                                <tr>
                                    <td>
                                        <strong>{{ $neighborhood->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $neighborhood->listings->count() }}</span>
                                    </td>
                                    <td>
                                        @if($neighborhood->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Pasif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.locations.neighborhoods.show', $neighborhood) }}" class="btn btn-info" title="Görüntüle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.locations.neighborhoods.edit', $neighborhood) }}" class="btn btn-warning" title="Düzenle">
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
                        <i class="fas fa-home fa-3x text-muted mb-3"></i>
                        <h5>Bu ilçede henüz mahalle bulunmuyor</h5>
                        <p class="text-muted">İlk mahalleyi eklemek için yukarıdaki butonu kullanın.</p>
                        <a href="{{ route('admin.locations.neighborhoods.create', ['district_id' => $district->id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>İlk Mahalleyi Ekle
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
