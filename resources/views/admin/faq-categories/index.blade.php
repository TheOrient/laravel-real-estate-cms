@extends('admin.layouts.app')

@section('title', 'FAQ Kategorileri')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">FAQ Kategorileri</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">FAQ Kategorileri</li>
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
                        <h3 class="card-title">FAQ Kategorileri</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.faq-categories.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> Yeni Kategori
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if($categories->count() > 0)
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kategori Adı</th>
                                        <th>Slug</th>
                                        <th>Durum</th>
                                        <th>Sıralama</th>
                                        <th>FAQ Sayısı</th>
                                        <th>Güncellenme</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categories as $category)
                                        <tr>
                                            <td>{{ $category->id }}</td>
                                            <td><strong>{{ $category->name }}</strong></td>
                                            <td><code>{{ $category->slug }}</code></td>
                                            <td>
                                                @if($category->is_active)
                                                    <span class="badge badge-success">Aktif</span>
                                                @else
                                                    <span class="badge badge-secondary">Pasif</span>
                                                @endif
                                            </td>
                                            <td>{{ $category->sort_order }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ $category->faqs->count() }}</span>
                                            </td>
                                            <td>{{ $category->updated_at->format('d.m.Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.faq-categories.edit', $category) }}" class="btn btn-warning btn-sm" title="Düzenle">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.faq-categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu kategoriyi silmek istediğinizden emin misiniz?')">
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
                                <i class="fas fa-folder fa-3x text-muted mb-3"></i>
                                <h5>Henüz kategori yok</h5>
                                <p class="text-muted">İlk kategoriyi oluşturun</p>
                                <a href="{{ route('admin.faq-categories.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i> Yeni Kategori
                                </a>
                            </div>
                        @endif
                    </div>
                    @if($categories->hasPages())
                        <div class="card-footer">
                            {{ $categories->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
