@auth
    @if (!auth()->user()->hasVerifiedEmail())
        <div class="bg-yellow-50 border-l-4 border-yellow-400">
            <div class="container mx-auto px-4 py-3">
                <div class="flex items-center justify-between flex-wrap">
                    <div class="flex items-center flex-1">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Email adresiniz onaylanmamış!</strong>
                                <span class="hidden sm:inline">Lütfen email adresinizi kontrol edin ve onay linkine tıklayın.</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 mt-2 sm:mt-0">
                        <form method="POST" action="{{ route('verification.send') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-yellow-700 hover:text-yellow-800 underline">
                                Tekrar Gönder
                            </button>
                        </form>
                        <a href="{{ route('verification.notice') }}" class="text-sm font-medium text-yellow-700 hover:text-yellow-800 underline">
                            Detaylar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endauth
