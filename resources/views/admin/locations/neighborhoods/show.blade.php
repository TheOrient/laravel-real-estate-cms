@extends('admin.layouts.app')

@section('title', 'Mahalle: ' . $neighborhood->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        Mahalle: {{ $neighborhood->name }}
        <small class="text-muted">/ {{ $neighborhood->district->name }}, {{ $neighborhood->district->city->name }}</small>
    </h1>
    <div class="btn-group">
        <a href="{{ route('admin.locations.neighborhoods.edit', $neighborhood) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Düzenle
        </a>
        <a href="{{ route('admin.locations.neighborhoods.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Geri Dön
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">İlan Sayısı</h6>
                        <h2 class="mb-0">{{ $neighborhood->listings->count() }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-list fa-2x"></i>
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
                        <h6 class="card-title">Aktif İlan</h6>
                        <h2 class="mb-0">{{ $neighborhood->listings->where('status', 'active')->count() }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
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
                        <h6 class="card-title">Onay Bekleyen</h6>
                        <h2 class="mb-0">{{ $neighborhood->listings->where('status', 'pending')->count() }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-{{ $neighborhood->is_active ? 'primary' : 'danger' }} text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Durum</h6>
                        <h5 class="mb-0">{{ $neighborhood->is_active ? 'Aktif' : 'Pasif' }}</h5>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-{{ $neighborhood->is_active ? 'check-circle' : 'times-circle' }} fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Neighborhood Details -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Mahalle Detayları
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>ID:</strong></td>
                        <td>{{ $neighborhood->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Adı:</strong></td>
                        <td>{{ $neighborhood->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Şehir:</strong></td>
                        <td>
                            <a href="{{ route('admin.locations.cities.show', $neighborhood->district->city) }}" class="text-decoration-none">
                                {{ $neighborhood->district->city->name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>İlçe:</strong></td>
                        <td>
                            <a href="{{ route('admin.locations.districts.show', $neighborhood->district) }}" class="text-decoration-none">
                                {{ $neighborhood->district->name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Slug:</strong></td>
                        <td><code>{{ $neighborhood->slug }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Durum:</strong></td>
                        <td>
                            @if($neighborhood->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Pasif</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Oluşturulma:</strong></td>
                        <td>{{ $neighborhood->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Son Güncelleme:</strong></td>
                        <td>{{ $neighborhood->updated_at->format('d.m.Y H:i') }}</td>
                    </tr>
                </table>

                <!-- Quick Actions -->
                <div class="mt-3">
                    <h6>Hızlı İşlemler:</h6>
                    <a href="{{ route('admin.locations.districts.show', $neighborhood->district) }}" class="btn btn-sm btn-info mb-2 d-block">
                        <i class="fas fa-map me-2"></i>{{ $neighborhood->district->name }} İlçesini Görüntüle
                    </a>
                    <a href="{{ route('admin.locations.cities.show', $neighborhood->district->city) }}" class="btn btn-sm btn-secondary mb-2 d-block">
                        <i class="fas fa-city me-2"></i>{{ $neighborhood->district->city->name }} Şehrini Görüntüle
                    </a>
                    <a href="{{ route('admin.locations.neighborhoods.index', ['district_id' => $neighborhood->district_id]) }}" class="btn btn-sm btn-warning mb-2 d-block">
                        <i class="fas fa-list me-2"></i>Aynı İlçedeki Diğer Mahalleler
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Listings Overview -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>İlanlar ({{ $neighborhood->listings->count() }})
                </h5>
                @if($neighborhood->listings->count() > 0)
                <a href="{{ route('admin.listings.index', ['neighborhood_id' => $neighborhood->id]) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye me-2"></i>Tüm İlanları Görüntüle
                </a>
                @endif
            </div>
            <div class="card-body">
                @if($neighborhood->listings->count() > 0)
                    <!-- Listings Status Chart -->
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h4 class="text-success mb-0">{{ $neighborhood->listings->where('status', 'active')->count() }}</h4>
                                <small class="text-muted">Aktif</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h4 class="text-warning mb-0">{{ $neighborhood->listings->where('status', 'pending')->count() }}</h4>
                                <small class="text-muted">Bekleyen</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h4 class="text-danger mb-0">{{ $neighborhood->listings->where('status', 'expired')->count() }}</h4>
                                <small class="text-muted">Süresi Dolmuş</small>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Listings -->
                    <h6>Son Eklenen İlanlar:</h6>
                    <div class="list-group">
                        @foreach($neighborhood->listings->take(5) as $listing)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ Str::limit($listing->title, 40) }}</h6>
                                <small class="text-muted">
                                    {{ $listing->created_at->format('d.m.Y') }} -
                                    @if($listing->status === 'active')
                                        <span class="text-success">Aktif</span>
                                    @elseif($listing->status === 'pending')
                                        <span class="text-warning">Bekleyen</span>
                                    @else
                                        <span class="text-danger">{{ ucfirst($listing->status) }}</span>
                                    @endif
                                </small>
                            </div>
                            <a href="{{ route('admin.listings.show', $listing) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>

                    @if($neighborhood->listings->count() > 5)
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.listings.index', ['neighborhood_id' => $neighborhood->id]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>{{ $neighborhood->listings->count() - 5 }} İlan Daha Var
                        </a>
                    </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-list fa-3x text-muted mb-3"></i>
                        <h5>Bu mahallede henüz ilan bulunmuyor</h5>
                        <p class="text-muted">İlanlar kullanıcılar tarafından eklendiğinde burada görünecektir.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Location Hierarchy -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-sitemap me-2"></i>Konum Hiyerarşisi
                </h5>
            </div>
            <div class="card-body">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.locations.cities.show', $neighborhood->district->city) }}">
                                <i class="fas fa-city me-1"></i>{{ $neighborhood->district->city->name }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.locations.districts.show', $neighborhood->district) }}">
                                <i class="fas fa-map me-1"></i>{{ $neighborhood->district->name }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="fas fa-home me-1"></i>{{ $neighborhood->name }}
                        </li>
                    </ol>
                </nav>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-city fa-2x text-primary mb-2"></i>
                                <h5>{{ $neighborhood->district->city->name }}</h5>
                                <p class="text-muted small">
                                    {{ $neighborhood->district->city->districts->count() }} İlçe,
                                    {{ $neighborhood->district->city->listings->count() }} İlan
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-map fa-2x text-info mb-2"></i>
                                <h5>{{ $neighborhood->district->name }}</h5>
                                <p class="text-muted small">
                                    {{ $neighborhood->district->neighborhoods->count() }} Mahalle,
                                    {{ $neighborhood->district->listings->count() }} İlan
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-home fa-2x text-success mb-2"></i>
                                <h5>{{ $neighborhood->name }}</h5>
                                <p class="text-muted small">
                                    {{ $neighborhood->listings->count() }} İlan
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
