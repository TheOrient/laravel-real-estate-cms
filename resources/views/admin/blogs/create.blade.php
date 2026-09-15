@extends('admin.layouts.app')

@section('title', __('admin/blog.create_post'))

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('admin/blog.create_post') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">{{ __('admin/blog.blog') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin/blog.create_post') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card">
            @include('admin.blogs.form')
        </div>
    </form>
</div>
@endsection
