@extends('admin.layouts.app')

@section('title', __('admin/categories.add_category'))

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('admin/categories.add_category') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">{{ __('admin/categories.categories') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin/categories.add_category') }}</li>
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
                        <h3 class="card-title">{{ __('admin/categories.category_details') }}</h3>
                    </div>
                    <form action="{{ route('admin.categories.store') }}" method="POST">
                        @csrf
                        @include('admin.categories.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection