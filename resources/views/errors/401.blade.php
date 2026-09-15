@extends('layouts.app')

@section('title', 'Yetkisiz Erişim')

@push('styles')
<meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="container mx-auto max-w-[1200px] px-4 py-16">
    <div class="max-w-md mx-auto text-center">
        <!-- Error illustration -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-yellow-50 mb-6">
                <i class="ri-lock-line text-6xl text-yellow-400"></i>
            </div>
            <h1 class="text-6xl font-bold text-gray-200 mb-2">401</h1>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Yetkisiz Erişim</h2>
            <p class="text-gray-600 mb-8">
                Bu sayfaya erişim yetkiniz bulunmuyor.
                Lütfen giriş yapın veya gerekli yetkilerle tekrar deneyin.
            </p>
        </div>

        <!-- Action buttons -->
        <div class="space-y-4">
            <a href="{{ route('login') }}"
               class="inline-flex items-center justify-center w-full bg-primary text-white px-6 py-3 rounded-button font-medium hover:bg-opacity-90 transition-colors">
                <i class="ri-login-box-line mr-2"></i>
                Giriş Yap
            </a>

            <a href="{{ route('home') }}"
               class="inline-flex items-center justify-center w-full bg-gray-100 text-gray-700 px-6 py-3 rounded-button font-medium hover:bg-gray-200 transition-colors">
                <i class="ri-home-line mr-2"></i>
                Ana Sayfaya Dön
            </a>
        </div>

        <!-- Register suggestion -->
        <div class="mt-8 pt-8 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-4">Henüz hesabınız yok mu?</p>
            <a href="{{ route('register') }}"
               class="text-sm text-primary hover:underline font-medium">
                Hemen Hesap Oluşturun
            </a>
        </div>
    </div>
</div>
@endsection
