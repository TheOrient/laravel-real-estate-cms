@extends('layouts.app')

@section('title', 'Çok Fazla İstek')

@section('content')
<div class="container mx-auto max-w-[1200px] px-4 py-16">
    <div class="max-w-md mx-auto text-center">
        <!-- Error illustration -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-blue-50 mb-6">
                <i class="ri-speed-line text-6xl text-blue-400"></i>
            </div>
            <h1 class="text-6xl font-bold text-gray-200 mb-2">429</h1>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Çok Fazla İstek</h2>
            <p class="text-gray-600 mb-8">
                Çok fazla istek gönderdiniz.
                Lütfen biraz bekleyin ve daha sonra tekrar deneyin.
            </p>
        </div>

        <!-- Countdown timer placeholder -->
        <div class="mb-8">
            <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-sm text-blue-600 mb-2">Tekrar deneyebilmek için:</p>
                <div class="text-2xl font-bold text-blue-800" id="countdown">60 saniye</div>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="space-y-4">
            <button onclick="window.location.reload()"
                    class="inline-flex items-center justify-center w-full bg-primary text-white px-6 py-3 rounded-button font-medium hover:bg-opacity-90 transition-colors">
                <i class="ri-refresh-line mr-2"></i>
                Tekrar Dene
            </button>

            <a href="{{ route('home') }}"
               class="inline-flex items-center justify-center w-full bg-gray-100 text-gray-700 px-6 py-3 rounded-button font-medium hover:bg-gray-200 transition-colors">
                <i class="ri-home-line mr-2"></i>
                Ana Sayfaya Dön
            </a>
        </div>

        <!-- Rate limit info -->
        <div class="mt-8 pt-8 border-t border-gray-200">
            <div class="text-sm text-gray-500">
                <p class="mb-2">Bu sınırlama neden var?</p>
                <p>Sunucularımızı korumak ve tüm kullanıcılar için hızlı bir deneyim sağlamak için istek sınırları uygulanmaktadır.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Simple countdown timer
    let timeLeft = 60;
    const countdownElement = document.getElementById('countdown');

    const timer = setInterval(function() {
        timeLeft--;
        if (timeLeft > 0) {
            countdownElement.textContent = timeLeft + ' saniye';
        } else {
            countdownElement.textContent = 'Tekrar deneyebilirsiniz';
            clearInterval(timer);
        }
    }, 1000);
</script>
@endpush
@endsection
