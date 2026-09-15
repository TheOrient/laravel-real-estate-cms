@extends('admin.layouts.app')

@section('title', 'Yeni FAQ')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Yeni FAQ</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.faqs.index') }}">FAQ'lar</a></li>
                        <li class="breadcrumb-item active">Yeni FAQ</li>
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
                        <h3 class="card-title">Yeni FAQ</h3>
                    </div>
                    <form action="{{ route('admin.faqs.store') }}" method="POST">
                        @csrf
                        @include('admin.faqs.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
