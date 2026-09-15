@extends('admin.layouts.app')

@section('title', 'FAQ Düzenle')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">FAQ Düzenle</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.faqs.index') }}">FAQ'lar</a></li>
                        <li class="breadcrumb-item active">Düzenle</li>
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
                        <h3 class="card-title">FAQ Düzenle</h3>
                    </div>
                    <form action="{{ route('admin.faqs.update', $faq) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.faqs.form', ['faq' => $faq])
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
