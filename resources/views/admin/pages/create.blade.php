@extends('admin.layouts.app')

@section('title', __('admin/pages.create_page'))

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('admin/pages.create_page') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">{{ __('admin/pages.pages') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin/pages.create_page') }}</li>
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
                        <h3 class="card-title">{{ __('admin/pages.create_page') }}</h3>
                    </div>
                    <form action="{{ route('admin.pages.store') }}" method="POST">
                        @csrf
                        @include('admin.pages.form', ['languages' => $languages, 'descriptions' => $descriptions, 'page' => $page ?? new \App\Models\Page()])
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- Summernote CSS -->
    <link href="{{ asset('assets/adminlte.3.2.0/plugins/summernote/summernote-bs4.min.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <!-- Summernote JS -->
    <script src="{{ asset('assets/adminlte.3.2.0/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('assets/adminlte.3.2.0/plugins/summernote/lang/summernote-tr-TR.min.js') }}"></script>

    <script>
        $(function () {
            // Initialize Summernote for all language content fields
            $('.summernote').summernote({
                height: 400,
                lang: 'tr-TR',
                placeholder: 'Sayfa içeriğini buraya yazın...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        // Image upload can be handled here if needed
                        // For now, we'll just insert as base64
                        for (let i = 0; i < files.length; i++) {
                            let reader = new FileReader();
                            reader.onloadend = function() {
                                $('#content').summernote('insertImage', reader.result);
                            }
                            reader.readAsDataURL(files[i]);
                        }
                    }
                }
            });

            // Auto-generate slug from title
            $('#title').on('keyup', function() {
                let title = $(this).val();
                let slug = title
                    .toLowerCase()
                    .replace(/ğ/g, 'g').replace(/ü/g, 'u').replace(/ş/g, 's')
                    .replace(/ı/g, 'i').replace(/ö/g, 'o').replace(/ç/g, 'c')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                $('#slug').val(slug);
            });
        });
    </script>
@endpush
