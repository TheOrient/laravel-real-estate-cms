{{-- Cookie Consent Banner --}}
<div id="cookieConsent" class="hidden fixed bottom-0 left-0 right-0 bg-white border-t-2 border-gray-200 shadow-2xl z-50 transform translate-y-full transition-transform duration-500 ease-out">
    <div class="container mx-auto px-4 py-4 max-w-[1320px]">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            {{-- Content --}}
            <div class="flex-1">
                <div class="flex items-start gap-3">
                    <i class="ri-cookie-line text-3xl text-primary flex-shrink-0 mt-1"></i>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-1">Çerez Kullanımı</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Web sitemizde deneyiminizi geliştirmek için çerezler kullanıyoruz.
                            Sitemizi kullanmaya devam ederek çerez kullanımını kabul etmiş olursunuz.
                            <a href="{{ route('pages.show', 'cerez-politikasi') }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:text-primary-dark underline font-medium">
                                Detaylı bilgi
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center gap-3 w-full md:w-auto">
                <button id="cookieReject" class="flex-1 md:flex-none px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200">
                    Reddet
                </button>
                <button id="cookieAccept" class="flex-1 md:flex-none px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-[#1E6F5C] to-[#13493E] hover:from-[#13493E] hover:to-[#0F1F1A] rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    Kabul Et
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cookieConsent = document.getElementById('cookieConsent');
        const cookieAccept = document.getElementById('cookieAccept');
        const cookieReject = document.getElementById('cookieReject');

        // Check if user has already made a choice
        const cookieChoice = localStorage.getItem('cookieConsent');

        // Show banner if no choice has been made
        if (!cookieChoice) {
            setTimeout(() => {
                cookieConsent.classList.remove('hidden');
                setTimeout(() => {
                    cookieConsent.classList.remove('translate-y-full');
                }, 100);
            }, 1000); // Show after 1 second
        }

        // Handle accept button
        cookieAccept.addEventListener('click', function() {
            localStorage.setItem('cookieConsent', 'accepted');
            hideBanner();
        });

        // Handle reject button
        cookieReject.addEventListener('click', function() {
            localStorage.setItem('cookieConsent', 'rejected');
            hideBanner();
        });

        // Hide banner with animation
        function hideBanner() {
            cookieConsent.classList.add('translate-y-full');
            setTimeout(() => {
                cookieConsent.classList.add('hidden');
            }, 500);
        }
    });
</script>
