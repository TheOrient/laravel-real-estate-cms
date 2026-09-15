@extends('admin.layouts.app')

@section('title', __('admin/users.all_users'))

@section('content_header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('admin/users.manage_team') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('admin/users.all_users') }}</li>
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
            <!-- Search and Filter Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin/users.search_users') }}</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.users.index') }}">
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                    <label class="btn btn-outline-primary {{ !$showTrashed ? 'active' : '' }}" style="width: 50%;">
                                        <input type="radio" name="show_trashed" value="0" {{ !$showTrashed ? 'checked' : '' }} onchange="this.form.submit()">
                                        <i class="fas fa-users"></i> {{ __('admin/users.active_users') }}
                                    </label>
                                    <label class="btn btn-outline-warning {{ $showTrashed ? 'active' : '' }}" style="width: 50%;">
                                        <input type="radio" name="show_trashed" value="1" {{ $showTrashed ? 'checked' : '' }} onchange="this.form.submit()">
                                        <i class="fas fa-trash-alt"></i> {{ __('admin/users.trashed_users') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control"
                                       placeholder="{{ __('admin/users.search_placeholder') }}"
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="role" class="form-control">
                                    <option value="">{{ __('admin/users.all_roles') }}</option>
                                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>{{ __('admin/users.admin') }}</option>
                                    <option value="agent" {{ request('role') == 'agent' ? 'selected' : '' }}>{{ __('admin/users.agent') }}</option>
                                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>{{ __('admin/users.user') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">{{ __('admin/users.all_statuses') }}</option>
                                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>{{ __('admin/users.verified') }}</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('admin/users.unverified') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> {{ __('admin/users.search') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if($showTrashed)
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i>
                <strong>Çöp Kutusu Görünümü:</strong> Bu kullanıcılar silinmiş durumda. Geri yükleyebilir veya kalıcı olarak silebilirsiniz.
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin/users.all_users') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-tie mr-1"></i> {{ __('admin/users.create_agent') }}
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>{{ __('admin/users.id') }}</th>
                                <th>{{ __('admin/users.name') }}</th>
                                <th>{{ __('admin/users.email') }}</th>
                                <th>{{ __('admin/users.role') }}</th>
                                <th>{{ __('admin/users.status') }}</th>
                                <th>{{ __('admin/users.created_at') }}</th>
                                <th>{{ __('admin/users.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr class="{{ $showTrashed ? 'table-danger' : '' }}">
                                <td>{{ $user->id }}</td>
                                <td>
                                    {{ $user->full_name }}
                                    @if($showTrashed)
                                    <span class="badge badge-warning ml-2">
                                        <i class="fas fa-trash-alt"></i> Silinmiş
                                    </span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->isAdmin())
                                    <span class="badge badge-primary">{{ __('admin/users.admin') }}</span>
                                    @elseif($user->isAgent())
                                    <span class="badge badge-success">{{ __('admin/users.agent') }}</span>
                                    @else
                                    <span class="badge badge-info">{{ __('admin/users.user') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_active)
                                    <span class="badge badge-success">{{ __('admin/users.active') }}</span>
                                    @else
                                    <span class="badge badge-danger">{{ __('admin/users.inactive') }}</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    @if (!$showTrashed)
                                        @if($user->id === 1)
                                            <!-- Super admin (ID 1) protection -->
                                            @if(auth()->id() === 1)
                                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-primary" title="Detayları Görüntüle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @else
                                                <span class="badge badge-warning" title="{{ __('admin/users.super_admin_protected') }}">
                                                    <i class="fas fa-shield-alt"></i> {{ __('admin/users.super_admin_protected') }}
                                                </span>
                                            @endif
                                        @else
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-primary" title="Detayları Görüntüle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            @if(auth()->id() !== $user->id)
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('{{ $user->full_name }} kullanıcısını çöp kutusuna taşımak istediğinize emin misiniz?\n\nÇöp kutusundan geri yükleyebilir veya kalıcı olarak silebilirsiniz.')" title="Çöp Kutusuna Taşı">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                            @endif
                                        @endif
                                    @else
                                        @if($user->id !== 1)
                                            <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('{{ $user->full_name }} kullanıcısını geri yüklemek istediğinize emin misiniz?')" title="Geri Yükle">
                                                    <i class="fas fa-undo"></i> Geri Yükle
                                                </button>
                                            </form>

                                            @if(auth()->id() !== $user->id)
                                            <form action="{{ route('admin.users.force-destroy', $user->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('⚠️ DİKKAT! ⚠️\n\n{{ $user->full_name }} kullanıcısını KALICI olarak silmek istediğinize emin misiniz?\n\n• Tüm ilanları silinecek\n• Tüm mesajları silinecek\n• Tüm siparişleri silinecek\n• Bu işlem GERİ ALINAMAZ!\n\nDevam etmek istediğinize emin misiniz?')" title="Kalıcı Olarak Sil">
                                                    <i class="fas fa-times"></i> Kalıcı Sil
                                                </button>
                                            </form>
                                            @endif
                                        @else
                                            <span class="badge badge-warning" title="{{ __('admin/users.super_admin_protected') }}">
                                                <i class="fas fa-shield-alt"></i> {{ __('admin/users.super_admin_protected') }}
                                            </span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
