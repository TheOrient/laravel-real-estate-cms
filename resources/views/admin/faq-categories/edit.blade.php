@extends('admin.layouts.app')

@section('title', 'FAQ Kategorisi Düzenle')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">FAQ Kategorisi Düzenle</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.faq-categories.index') }}">FAQ Kategorileri</a></li>
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
                        <h3 class="card-title">FAQ Kategorisi Düzenle</h3>
                    </div>
                    <form action="{{ route('admin.faq-categories.update', $faqCategory) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.faq-categories.form', ['faqCategory' => $faqCategory])
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
