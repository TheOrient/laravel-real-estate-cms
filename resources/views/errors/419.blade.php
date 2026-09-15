@extends('layouts.app')

@section('title', 'Sayfa Süresi Doldu')

@section('content')
<div class="container mx-auto max-w-[1200px] px-4 py-16">
    <div class="max-w-md mx-auto text-center">
        <!-- Error illustration -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-orange-50 mb-6">
                <i class="ri-time-line text-6xl text-orange-400"></i>
            </div>
            <h1 class="text-6xl font-bold text-gray-200 mb-2">419</h1>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Sayfa Süresi Doldu</h2>
            <p class="text-gray-600 mb-8">
                Güvenlik sebebiyle oturum süreniz dolmuş.
                Lütfen sayfayı yenileyip işleminizi tekrar deneyin.
            </p>
        </div>

        <!-- Action buttons -->
        <div class="space-y-4">
            <button onclick="window.location.reload()"
                    class="inline-flex items-center justify-center w-full bg-primary text-white px-6 py-3 rounded-button font-medium hover:bg-opacity-90 transition-colors">
                <i class="ri-refresh-line mr-2"></i>
                Sayfayı Yenile
            </button>

            <a href="{{ route('home') }}"
               class="inline-flex items-center justify-center w-full bg-gray-100 text-gray-700 px-6 py-3 rounded-button font-medium hover:bg-gray-200 transition-colors">
                <i class="ri-home-line mr-2"></i>
                Ana Sayfaya Dön
            </a>
        </div>

        <!-- Security info -->
        <div class="mt-8 pt-8 border-t border-gray-200">
            <div class="flex items-start gap-3 text-left">
                <i class="ri-shield-check-line text-green-500 text-lg mt-0.5"></i>
                <div class="text-sm text-gray-600">
                    <p class="font-medium mb-1">Güvenliğiniz için:</p>
                    <p>Form verileri belirli bir süre sonra sona erer. Bu durum kişisel bilgilerinizi korumak içindir.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
