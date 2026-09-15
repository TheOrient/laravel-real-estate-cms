@php
    $captchaService = app(\App\Services\CaptchaService::class);
    $provider = $captchaService->getProvider();
    $isEnabled = $captchaService->isEnabled($formType ?? 'contact');
    $siteKey = $captchaService->getSiteKey();
@endphp

@if($isEnabled && $provider !== 'none' && $siteKey)
    <div class="captcha-container mb-4">
        @if($provider === 'recaptcha_v2')
            <div class="g-recaptcha" data-sitekey="{{ $siteKey }}"></div>
            <input type="hidden" name="captcha_response" id="captcha_response_{{ $formType }}">

        @elseif($provider === 'recaptcha_v3')
            <input type="hidden" name="captcha_response" id="captcha_response_{{ $formType }}">

        @elseif($provider === 'recaptcha_v2_invisible')
            <div id="recaptcha_v2_invisible_{{ $formType }}" class="g-recaptcha"
                data-sitekey="{{ $siteKey }}"
                data-callback="onRecaptchaV2InvisibleSuccess_{{ $formType }}"
                data-size="invisible">
            </div>
            <input type="hidden" name="captcha_response" id="captcha_response_{{ $formType }}">

        @elseif($provider === 'turnstile')
            <div class="cf-turnstile" data-sitekey="{{ $siteKey }}" data-callback="onTurnstileSuccess_{{ $formType }}"></div>
            <input type="hidden" name="captcha_response" id="captcha_response_{{ $formType }}">
        @endif

        @error('captcha_response')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @push('scripts')
        @if($provider === 'recaptcha_v2')
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Find the form that contains this captcha input
                    const captchaInput = document.getElementById('captcha_response_{{ $formType }}');
                    if (!captchaInput) {
                        console.error('Captcha input not found for form type: {{ $formType }}');
                        return;
                    }

                    const form = captchaInput.closest('form');
                    if (!form) {
                        console.error('Form not found for captcha input');
                        return;
                    }

                    form.addEventListener('submit', function (e) {
                        const response = grecaptcha.getResponse();
                        captchaInput.value = response;

                        if (!response) {
                            console.warn('reCAPTCHA v2: No response from grecaptcha');
                        } else {
                            console.log('reCAPTCHA v2 token received for {{ $formType }}');
                        }
                    });
                });
            </script>

        @elseif($provider === 'recaptcha_v3')
            <script src="https://www.google.com/recaptcha/api.js?render={{ $siteKey }}" async defer></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Find the form that contains this captcha input
                    const captchaInput = document.getElementById('captcha_response_{{ $formType }}');
                    if (!captchaInput) {
                        console.error('Captcha input not found for form type: {{ $formType }}');
                        return;
                    }

                    const form = captchaInput.closest('form');
                    if (!form) {
                        console.error('Form not found for captcha input');
                        return;
                    }

                    // Flag to prevent multiple submissions
                    let isSubmitting = false;

                    form.addEventListener('submit', function (e) {
                        // If already submitting or token exists, allow submission
                        if (isSubmitting || captchaInput.value) {
                            return true;
                        }

                        // Prevent default submission
                        e.preventDefault();
                        e.stopPropagation();

                        // Set submitting flag
                        isSubmitting = true;

                        // Execute reCAPTCHA
                        grecaptcha.ready(function () {
                            grecaptcha.execute('{{ $siteKey }}', { action: '{{ $formType }}' })
                                .then(function (token) {
                                    console.log('reCAPTCHA token received for {{ $formType }}');
                                    captchaInput.value = token;

                                    // Reset flag and submit form
                                    isSubmitting = false;
                                    form.submit();
                                })
                                .catch(function (error) {
                                    console.error('reCAPTCHA error:', error);
                                    isSubmitting = false;
                                    alert('CAPTCHA doğrulaması başarısız. Lütfen sayfayı yenileyip tekrar deneyin.');
                                });
                        });

                        return false;
                    });
                });
            </script>

        @elseif($provider === 'recaptcha_v2_invisible')
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <script>
                // Callback function for successful verification
                window.onRecaptchaV2InvisibleSuccess_{{ $formType }} = function(token) {
                    console.log('reCAPTCHA v2 Invisible token received for {{ $formType }}');
                    const captchaInput = document.getElementById('captcha_response_{{ $formType }}');
                    if (captchaInput) {
                        captchaInput.value = token;
                        // Submit the form
                        const form = captchaInput.closest('form');
                        if (form) {
                            // Remove the event listener to prevent infinite loop
                            form.removeEventListener('submit', form._recaptchaSubmitHandler);
                            form.submit();
                        }
                    }
                };

                document.addEventListener('DOMContentLoaded', function () {
                    // Find the form that contains this captcha
                    const captchaInput = document.getElementById('captcha_response_{{ $formType }}');
                    if (!captchaInput) {
                        console.error('Captcha input not found for form type: {{ $formType }}');
                        return;
                    }

                    const form = captchaInput.closest('form');
                    if (!form) {
                        console.error('Form not found for captcha input');
                        return;
                    }

                    // Store the handler so we can remove it later
                    form._recaptchaSubmitHandler = function(e) {
                        // If token already exists, allow submission
                        if (captchaInput.value) {
                            return true;
                        }

                        // Prevent default submission
                        e.preventDefault();
                        e.stopPropagation();

                        // Execute invisible reCAPTCHA
                        grecaptcha.execute();

                        return false;
                    };

                    form.addEventListener('submit', form._recaptchaSubmitHandler);
                });
            </script>

        @elseif($provider === 'turnstile')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            <script>
                window.onTurnstileSuccess_{{ $formType }} = function (token) {
                    document.getElementById('captcha_response_{{ $formType }}').value = token;
                };
            </script>
        @endif
    @endpush
@endif