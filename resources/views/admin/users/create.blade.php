@extends('admin.layouts.app')

@section('title', __('admin/users.create_agent'))

@section('content_header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('admin/users.create_agent') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">{{ __('admin/users.all_users') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('admin/users.create_agent') }}</li>
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
                    <h3 class="card-title">{{ __('admin/users.create_agent') }}</h3>
                </div>
                <div class="card-body border-bottom">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-user-tie mr-1"></i>{{ __('admin/users.agent_creation_notice') }}
                    </div>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @include('admin.users.form')
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
