@extends('admin.layouts.app')

@section('title', $user->name . ' - Kullanıcı Detayı')

@section('content_header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('admin/users.user_details') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">{{ __('admin/users.all_users') }}</a></li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-4">
            <!-- User Profile Card -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{ $user->avatar_large_url }}"
                             alt="{{ $user->name }}">
                    </div>

                    <h3 class="profile-username text-center">{{ $user->name }}</h3>

                    <p class="text-muted text-center">
                        @if($user->user_role === 'admin')
                            <span class="badge badge-danger">{{ __('admin/users.admin') }}</span>
                        @else
                            <span class="badge badge-info">{{ __('admin/users.agent') }}</span>
                        @endif

                        @if(!$user->is_active)
                            <span class="badge badge-warning">{{ __('admin/users.inactive') }}</span>
                        @endif
                    </p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>{{ __('admin/users.total_listings') }}</b> <a class="float-right">{{ $stats['total_listings'] }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>{{ __('admin/users.active_listings') }}</b> <a class="float-right text-success">{{ $stats['active_listings'] }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>{{ __('admin/users.pending_listings') }}</b> <a class="float-right text-warning">{{ $stats['pending_listings'] }}</a>
                        </li>
                    </ul>

                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-block"><i class="fas fa-edit"></i> <b>{{ __('admin/users.edit') }}</b></a>
                </div>
            </div>

            <!-- User Info Card -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin/users.user_info') }}</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                    <p class="text-muted">
                        {{ $user->email }}
                        @if($user->email_verified_at)
                            <span class="badge badge-success badge-sm">Doğrulanmış</span>
                        @else
                            <span class="badge badge-warning badge-sm">Doğrulanmamış</span>
                        @endif
                    </p>

                    <hr>

                    <strong><i class="fas fa-phone mr-1"></i> Telefon</strong>
                    <p class="text-muted">
                        {{ $user->phone ?? '-' }}
                        @if($user->phone && $user->enable_whatsapp === 'yes')
                            <span class="badge badge-success badge-sm"><i class="fab fa-whatsapp"></i> WhatsApp</span>
                        @endif
                    </p>

                    <hr>

                    <strong><i class="fas fa-calendar mr-1"></i> Üyelik Tarihi</strong>
                    <p class="text-muted">{{ $user->created_at->format('d.m.Y H:i') }}</p>

                    <hr>

                    <strong><i class="fas fa-clock mr-1"></i> Son Güncelleme</strong>
                    <p class="text-muted">{{ $user->updated_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-8">
            <!-- Recent Listings -->
            @if($user->listings->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list mr-2"></i>Son İlanlar</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Başlık</th>
                                <th>Kategori</th>
                                <th>Fiyat</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->listings as $listing)
                            <tr>
                                <td>
                                    <strong>{{ $listing->title }}</strong>
                                    @if($listing->is_featured)
                                        <span class="badge badge-warning badge-sm">Öne Çıkan</span>
                                    @endif
                                </td>
                                <td>{{ $listing->category->name }}</td>
                                <td>
                                    @if($listing->price)
                                        <strong>{{ number_format($listing->price, 2) }} ₺</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($listing->is_approved)
                                        <span class="badge badge-success">Onaylı</span>
                                    @else
                                        <span class="badge badge-warning">Bekliyor</span>
                                    @endif

                                    @if(!$listing->is_active)
                                        <span class="badge badge-secondary">Pasif</span>
                                    @endif
                                </td>
                                <td>{{ $listing->created_at->format('d.m.Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.listings.show', $listing) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
<script>
    $(document).ready(function() {
        toastr.success('{{ session('success') }}');
    });
</script>
@endif

@if(session('error'))
<script>
    $(document).ready(function() {
        toastr.error('{{ session('error') }}');
    });
</script>
@endif
@endsection
