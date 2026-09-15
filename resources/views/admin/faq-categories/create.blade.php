@extends('admin.layouts.app')

@section('title', 'Yeni FAQ Kategorisi')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Yeni FAQ Kategorisi</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.faq-categories.index') }}">FAQ Kategorileri</a></li>
                        <li class="breadcrumb-item active">Yeni Kategori</li>
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
                        <h3 class="card-title">Yeni FAQ Kategorisi</h3>
                    </div>
                    <form action="{{ route('admin.faq-categories.store') }}" method="POST">
                        @csrf
                        @include('admin.faq-categories.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
