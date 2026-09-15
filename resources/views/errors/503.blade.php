@extends('layouts.app')

@section('title', 'Hizmet Kullanılamıyor')

@section('content')
<div class="container mx-auto max-w-[1200px] px-4 py-16">
    <div class="max-w-md mx-auto text-center">
        <!-- Error illustration -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-purple-50 mb-6">
                <i class="ri-settings-3-line text-6xl text-purple-400"></i>
            </div>
            <h1 class="text-6xl font-bold text-gray-200 mb-2">503</h1>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Bakım Modu</h2>
            <p class="text-gray-600 mb-8">
                Sitemiz şu anda bakım modunda.
                Daha iyi hizmet verebilmek için sistemi güncelliyoruz.
            </p>
        </div>

        <!-- Maintenance info -->
        <div class="mb-8">
            <div class="bg-purple-50 rounded-lg p-6">
                <i class="ri-tools-line text-3xl text-purple-500 mb-3"></i>
                <h3 class="font-semibold text-purple-800 mb-2">Sistem Güncellemesi</h3>
                <p class="text-sm text-purple-600">
                    Size daha iyi hizmet verebilmek için sistemimizi güncelliyoruz.
                    Kısa süre içinde tekrar hizmetinizdeyiz.
                </p>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="space-y-4">
            <button onclick="window.location.reload()"
                    class="inline-flex items-center justify-center w-full bg-primary text-white px-6 py-3 rounded-button font-medium hover:bg-opacity-90 transition-colors">
                <i class="ri-refresh-line mr-2"></i>
                Tekrar Kontrol Et
            </button>

            <div class="flex space-x-3">
                <a href="#"
                   class="flex-1 inline-flex items-center justify-center bg-gray-100 text-gray-700 px-4 py-3 rounded-button text-sm font-medium hover:bg-gray-200 transition-colors">
                    <i class="ri-twitter-fill mr-2"></i>
                    Twitter
                </a>
                <a href="#"
                   class="flex-1 inline-flex items-center justify-center bg-gray-100 text-gray-700 px-4 py-3 rounded-button text-sm font-medium hover:bg-gray-200 transition-colors">
                    <i class="ri-mail-line mr-2"></i>
                    E-posta
                </a>
            </div>
        </div>

        <!-- Status updates -->
        <div class="mt-8 pt-8 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-4">Güncellemeler için:</p>
            <div class="flex justify-center space-x-4">
                <a href="#" class="text-sm text-primary hover:underline">
                    <i class="ri-notification-line mr-1"></i>
                    Durum Sayfası
                </a>
                <a href="#" class="text-sm text-primary hover:underline">
                    <i class="ri-rss-line mr-1"></i>
                    RSS
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
