@extends('admin.layouts.app')

@section('title', __('admin/blog.blog'))

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('admin/blog.all_posts') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin/blog.blog') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('admin/blog.list_posts') }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> {{ __('admin/blog.new_post') }}
                    </a>
                </div>
            </div>

            {{-- Filters --}}
            <div class="card-body border-bottom">
                <form method="GET" class="form-inline" action="{{ route('admin.blogs.index') }}">
                    <div class="form-group mr-2 mb-2">
                        <input type="text" name="q" value="{{ request('q') }}"
                               class="form-control form-control-sm"
                               placeholder="{{ __('admin/blog.search_placeholder') }}">
                    </div>
                    <div class="form-group mr-2 mb-2">
                        <select name="status" class="form-control form-control-sm">
                            <option value="">{{ __('admin/blog.all_statuses') }}</option>
                            <option value="draft" @selected(request('status') === 'draft')>{{ __('admin/blog.status_draft') }}</option>
                            <option value="published" @selected(request('status') === 'published')>{{ __('admin/blog.status_published') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-default mb-2">
                        <i class="fas fa-filter mr-1"></i> {{ __('admin/blog.filter') }}
                    </button>
                </form>
            </div>

            <div class="card-body table-responsive p-0">
                @if($posts->total() > 0)
                    <table class="table table-hover text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('admin/blog.column_id') }}</th>
                                <th>{{ __('admin/blog.column_image') }}</th>
                                <th>{{ __('admin/blog.column_title') }}</th>
                                <th>{{ __('admin/blog.column_status') }}</th>
                                <th>{{ __('admin/blog.column_views') }}</th>
                                <th>{{ __('admin/blog.column_published_at') }}</th>
                                <th>{{ __('admin/blog.column_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posts as $post)
                                @php
                                    $desc = $post->description ?? $post->descriptions->first();
                                @endphp
                                <tr>
                                    <td>{{ $post->id }}</td>
                                    <td>
                                        @if($post->image)
                                            <img src="{{ route('image.resize', ['size' => 'thumbnail_small', 'fit' => 'crop', 'path' => $post->image]) }}"
                                                 alt="" style="width:56px; height:40px; object-fit:cover; border-radius:4px;">
                                        @else
                                            <span class="text-muted"><i class="fas fa-image"></i></span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $desc->title ?? '—' }}</strong>
                                        @if($desc?->slug)
                                            <br><small class="text-muted"><code>{{ $desc->slug }}</code></small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($post->status === 'published' && $post->is_active)
                                            <span class="badge badge-success">{{ __('admin/blog.status_published') }}</span>
                                        @elseif($post->status === 'published' && !$post->is_active)
                                            <span class="badge badge-warning">{{ __('admin/blog.is_active') }}: ✗</span>
                                        @else
                                            <span class="badge badge-secondary">{{ __('admin/blog.status_draft') }}</span>
                                        @endif
                                        @if($post->is_featured)
                                            <span class="badge badge-info ml-1"><i class="fas fa-star"></i></span>
                                        @endif
                                    </td>
                                    <td>{{ number_format((int) $post->view_count) }}</td>
                                    <td>
                                        @if($post->published_at)
                                            {{ $post->published_at->format('d.m.Y H:i') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($desc?->slug)
                                                <a href="{{ route('blog.show', $desc->slug) }}" target="_blank"
                                                   class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.blogs.edit', $post) }}"
                                               class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.blogs.destroy', $post) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('{{ __('admin/blog.delete_confirm') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
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
                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                        <h5>{{ __('admin/blog.no_posts') }}</h5>
                        <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-plus mr-1"></i> {{ __('admin/blog.new_post') }}
                        </a>
                    </div>
                @endif
            </div>

            @if($posts->hasPages())
                <div class="card-footer">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
