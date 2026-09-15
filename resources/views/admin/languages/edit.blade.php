@extends('admin.layouts.app')

@section('title', __('admin/languages.edit_language'))

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('admin/languages.edit_language') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.languages.index') }}">{{ __('admin/languages.languages') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin/languages.edit_language') }}</li>
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
                        <h3 class="card-title">{{ __('admin/languages.edit_language') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-secondary">
                            {{ __('admin/languages.seeded_notice') }}
                        </div>
                        <form action="{{ route('admin.languages.update', $language) }}" method="POST" enctype="multipart/form-data">
                            <fieldset disabled>
                            @include('admin.languages.form', ['language' => $language])
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
