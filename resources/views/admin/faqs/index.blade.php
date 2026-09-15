@extends('admin.layouts.app')

@section('title', 'FAQ Yönetimi')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">FAQ Yönetimi</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">FAQ'lar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">FAQ'lar</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> Yeni FAQ
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <!-- Filters -->
                        <form method="GET" action="{{ route('admin.faqs.index') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="category" class="form-control">
                                        <option value="">Tüm Kategoriler</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-control">
                                        <option value="">Tüm Durumlar</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Pasif</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Soru veya cevapta ara..."
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-info btn-block">
                                        <i class="fas fa-search"></i> Filtrele
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            @if($faqs->count() > 0)
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Soru</th>
                                            <th>Kategori</th>
                                            <th>Durum</th>
                                            <th>Sıralama</th>
                                            <th>Güncellenme</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($faqs as $faq)
                                            <tr>
                                                <td>{{ $faq->id }}</td>
                                                <td>
                                                    <strong>{{ Str::limit($faq->question, 60) }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary">{{ $faq->category->name }}</span>
                                                </td>
                                                <td>
                                                    @if($faq->is_active)
                                                        <span class="badge badge-success">Aktif</span>
                                                    @else
                                                        <span class="badge badge-secondary">Pasif</span>
                                                    @endif
                                                </td>
                                                <td>{{ $faq->sort_order }}</td>
                                                <td>{{ $faq->updated_at->format('d.m.Y H:i') }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-warning btn-sm" title="Düzenle">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu FAQ\'yı silmek istediğinizden emin misiniz?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" title="Sil">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                    <h5>Henüz FAQ yok</h5>
                                    <p class="text-muted">İlk FAQ'yı oluşturun</p>
                                    <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus mr-1"></i> Yeni FAQ
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($faqs->hasPages())
                        <div class="card-footer">
                            {{ $faqs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
