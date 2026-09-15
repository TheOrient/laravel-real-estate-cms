@extends('admin.layouts.app')

@section('title', 'Şehir Yönetimi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Şehir Yönetimi</h1>
    <a href="{{ route('admin.locations.cities.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Yeni Şehir Ekle
    </a>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Toplam Şehir</h6>
                        <h2 class="mb-0">{{ $cities->total() }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-city fa-2x"></i>
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
                        <h6 class="card-title">Aktif Şehir</h6>
                        <h2 class="mb-0">{{ $cities->where('is_active', 1)->count() }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cities Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Şehirler</h5>
    </div>
    <div class="card-body">
        @if($cities->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Şehir Adı</th>
                            <th>Slug</th>
                            <th>İlçe Sayısı</th>
                            <th>İlan Sayısı</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cities as $city)
                        <tr>
                            <td>{{ $city->id }}</td>
                            <td>
                                <strong>{{ $city->name }}</strong>
                            </td>
                            <td>
                                <code>{{ $city->slug }}</code>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $city->districts_count }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $city->listings_count }}</span>
                            </td>
                            <td>
                                @if($city->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Pasif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.locations.cities.show', $city) }}" class="btn btn-sm btn-info" title="Görüntüle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.locations.cities.edit', $city) }}" class="btn btn-sm btn-warning" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.locations.cities.destroy', $city) }}" method="POST" style="display: inline;" onsubmit="return confirm('Bu şehri silmek istediğinizden emin misiniz?')">
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
                {{ $cities->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-city fa-3x text-muted mb-3"></i>
                <h5>Henüz şehir eklenmemiş</h5>
                <p class="text-muted">İlk şehri eklemek için yukarıdaki butonu kullanın.</p>
                <a href="{{ route('admin.locations.cities.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>İlk Şehri Ekle
                </a>
            </div>
        @endif
    </div>
</div>
@endsection