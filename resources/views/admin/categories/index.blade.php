@extends('admin.layouts.app')

@section('title', __('admin/categories.categories'))

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('admin/categories.categories') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin/categories.categories') }}</li>
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
                        <h3 class="card-title">{{ __('admin/categories.all_categories') }}</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> {{ __('admin/categories.add_category') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('admin/categories.icon') }}</th>
                                    <th>{{ __('admin/categories.name') }}</th>
                                    <th>{{ __('admin/categories.parent_category') }}</th>
                                    <th>{{ __('admin/categories.listing_count') }}</th>
                                    <th>{{ __('admin/categories.is_filterable') }}</th>
                                    <th>{{ __('admin/general.status') }}</th>
                                    <th>{{ __('admin/general.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $category)
                                    <tr>
                                        <td>{{ $category->id }}</td>
                                        <td>
                                            @if ($category->icon)
                                                <i class="{{ $category->icon }}"></i> <code>{{ $category->icon }}</code>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $category->name }}</td>
                                        <td>
                                            @if ($category->parent)
                                                {{ $category->parent->name }}
                                            @else
                                                <span class="text-muted">{{ __('admin/categories.no_parent') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($category->listings_count) }}</td>
                                        <td>
                                            @if ($category->is_filterable)
                                                <span class="badge badge-success">{{ __('admin/general.yes') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ __('admin/general.no') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($category->is_active)
                                                <span class="badge badge-success">{{ __('admin/general.active') }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ __('admin/general.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('categories.show', $category->slug) }}" class="btn btn-info btn-sm" target="_blank" title="{{ __('admin/general.view') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary btn-sm" title="{{ __('admin/general.edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('admin/categories.confirm_delete') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="{{ __('admin/general.delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">{{ __('admin/categories.no_categories') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($categories->hasPages())
                        <div class="card-footer">
                            {{ $categories->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection