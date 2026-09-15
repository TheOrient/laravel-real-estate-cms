@extends('admin.layouts.app')

@section('title', __('admin/users.edit_user'))

@section('content_header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('admin/users.edit_user') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">{{ __('admin/users.all_users') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('admin/users.edit_user') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin/users.edit_user') }}: {{ $user->full_name }}</h3>
                </div>
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @include('admin.users.form')
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection