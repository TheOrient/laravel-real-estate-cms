@extends('admin.layouts.app')

@section('title', __('admin/pages.title'))

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('admin/pages.manage_pages') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin/pages.pages') }}</li>
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
                        <h3 class="card-title">{{ __('admin/pages.pages') }}</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.pages.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> {{ __('admin/pages.create_page') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        @if($pages->count() > 0)
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>{{ __('admin/pages.page_title') }}</th>
                                        <th>{{ __('admin/pages.slug') }}</th>
                                        <th>{{ __('admin/pages.status') }}</th>
                                        <th>{{ __('admin/pages.sort_order') }}</th>
                                        <th>{{ __('admin/pages.updated_at') }}</th>
                                        <th>{{ __('admin/pages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pages as $page)
                                        <tr>
                                            <td>{{ $page->id }}</td>
                                            @php
                                                $desc = $page->description ?? $page->descriptions->first();
                                            @endphp
                                            <td>
                                                <strong>{{ $desc->title ?? $page->title }}</strong>
                                                @if($desc?->meta_description)
                                                    <br><small class="text-muted">{{ Str::limit($desc->meta_description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <code>{{ $desc->slug ?? '-' }}</code>
                                            </td>
                                            <td>
                                                @if($page->is_active)
                                                    <span class="badge badge-success">{{ __('admin/pages.active') }}</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ __('admin/pages.inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $page->sort_order }}</td>
                                            <td>{{ $page->updated_at->format('d.m.Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.pages.show', $page) }}" class="btn btn-info btn-sm" title="{{ __('admin/pages.view_page') }}" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-warning btn-sm" title="{{ __('admin/pages.edit_page') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('admin/pages.delete_confirm') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="{{ __('admin/pages.delete_page') }}">
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
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <h5>{{ __('admin/pages.no_pages') }}</h5>
                                <p class="text-muted">{{ __('admin/pages.create_first_page') }}</p>
                                <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i> {{ __('admin/pages.create_page') }}
                                </a>
                            </div>
                        @endif
                    </div>
                    @if($pages->hasPages())
                        <div class="card-footer">
                            {{ $pages->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
