@extends('layouts.app')

@section('title', 'Erişim Yasak')

@push('styles')
<meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="container mx-auto max-w-[1200px] px-4 py-16">
    <div class="max-w-md mx-auto text-center">
        <!-- Error illustration -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-red-50 mb-6">
                <i class="ri-forbid-line text-6xl text-red-400"></i>
            </div>
            <h1 class="text-6xl font-bold text-gray-200 mb-2">403</h1>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Erişim Yasak</h2>
            <p class="text-gray-600 mb-8">
                Bu sayfaya erişim izniniz bulunmuyor.
                Bu işlem için gerekli yetkilere sahip değilsiniz.
            </p>
        </div>

        <!-- Action buttons -->
        <div class="space-y-4">
            <a href="{{ route('home') }}"
               class="inline-flex items-center justify-center w-full bg-primary text-white px-6 py-3 rounded-button font-medium hover:bg-opacity-90 transition-colors">
                <i class="ri-home-line mr-2"></i>
                Ana Sayfaya Dön
            </a>

            <button onclick="history.back()"
                    class="inline-flex items-center justify-center w-full bg-gray-100 text-gray-700 px-6 py-3 rounded-button font-medium hover:bg-gray-200 transition-colors">
                <i class="ri-arrow-left-line mr-2"></i>
                Geri Git
            </button>
        </div>

        <!-- Contact info -->
        <div class="mt-8 pt-8 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-4">Bu bir hata olduğunu düşünüyorsanız:</p>
            <div class="flex justify-center space-x-4">
                <a href="#" class="text-sm text-primary hover:underline">
                    <i class="ri-customer-service-line mr-1"></i>
                    Destek Talebi
                </a>
                <a href="#" class="text-sm text-primary hover:underline">
                    <i class="ri-mail-line mr-1"></i>
                    İletişim
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
