@extends('admin.layouts.app')

@section('title', __('admin/languages.languages'))

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('admin/languages.language_management') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin/languages.languages') }}</li>
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
                        <h3 class="card-title">{{ __('admin/languages.all_languages') }}</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>{{ __('admin/languages.icon') }}</th>
                                <th>{{ __('admin/languages.title') }}</th>
                                <th>{{ __('admin/languages.sort_order') }}</th>
                                <th>{{ __('admin/general.status') }}</th>
                                <th>{{ __('admin/general.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($languages as $language)
                                <tr>
                                    <td>{{ $language->id }}</td>
                                    <td>
                                        @php
                                            $iconUrl = language_icon_url($language);
                                        @endphp
                                        @if($iconUrl)
                                            <img src="{{ $iconUrl }}" alt="{{ $language->title }}" width="32" height="32" class="img-circle">
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $language->title }}</td>
                                    <td>{{ $language->sort_order }}</td>
                                    <td>
                                        @if($language->is_active)
                                            <span class="badge badge-success">{{ __('admin/general.active') }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ __('admin/general.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.languages.edit', $language) }}" class="btn btn-primary btn-sm" title="{{ __('admin/general.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">{{ __('admin/languages.no_languages') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($languages->hasPages())
                        <div class="card-footer">
                            {{ $languages->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
