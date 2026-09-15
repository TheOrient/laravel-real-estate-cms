@extends('layouts.app')

@section('title', __('user.profile'))

@section('content')
<div class="container mx-auto px-4 max-w-[1200px] py-8">
    @include('user.partials.navigation', ['active' => 'profile'])

    <!-- Enhanced Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">👤 {{ __('user.profile_information') }}</h1>
        <p class="mt-2 text-lg text-gray-600">{{ __('user.view_update_profile') }}</p>
    </div>

    <div class="max-w-6xl mx-auto space-y-8">
        <!-- Profile Information Card -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h3 class="text-2xl font-bold text-[#1A1A1A] mb-6" style="font-family: Inter, sans-serif;">{{ __('user.personal_information') }}</h3>

            <form action="{{ route('user.profile.update') }}" method="POST" class="space-y-6" enctype="multipart/form-data">

                {{-- Profil fotoğrafı — ilan detay sayfasındaki danışman
                     kartında bu görsel kullanılır. --}}
                <div class="flex items-center gap-5 pb-6 border-b border-gray-200">
                    <img src="{{ $user->avatar_large_url }}" alt="{{ $user->first_name }}"
                         width="88" height="88"
                         style="width:5.5rem; height:5.5rem; border-radius:9999px; object-fit:cover; object-position:top; border:4px solid #fff; box-shadow:0 10px 24px -10px rgba(15,31,26,.32);">
                    <div class="flex-1">
                        <label for="avatar" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('user.profile_photo') }}</label>
                        <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/webp"
                               class="w-full text-sm text-[#1A1A1A] file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#1E6F5C] file:text-white hover:file:bg-[#13493E] cursor-pointer">
                        <small class="text-gray-500" style="font-family: Inter, sans-serif;">{{ __('user.profile_photo_help') }}</small>
                        @error('avatar')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
                @csrf

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('user.first_name') }}</label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}"
                               class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all @error('first_name') border-red-500 @enderror"
                               required placeholder="{{ __('user.first_name_placeholder') }}" style="font-family: Inter, sans-serif;">
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('user.last_name') }}</label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}"
                               class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all @error('last_name') border-red-500 @enderror"
                               required placeholder="{{ __('user.last_name_placeholder') }}" style="font-family: Inter, sans-serif;">
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('user.email') }}</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                               class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all @error('email') border-red-500 @enderror"
                               required placeholder="{{ __('user.email_placeholder') }}" style="font-family: Inter, sans-serif;">
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('user.phone') }}</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->formatted_phone ?? $user->phone) }}"
                               class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all @error('phone') border-red-500 @enderror"
                               placeholder="{{ __('user.phone_placeholder') }}" style="font-family: Inter, sans-serif;">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bio -->
                    <div class="sm:col-span-2">
                        <label for="bio" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">
                            {{ __('user.bio') }}
                        </label>
                        <textarea name="bio" id="bio" rows="4"
                                  class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all @error('bio') border-red-500 @enderror"
                                  placeholder="{{ __('user.bio_placeholder') }}"
                                  style="font-family: Inter, sans-serif;">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-[#E0E0E0]">
                    <button type="submit" class="px-6 py-3 bg-[#1E6F5C] text-white text-sm font-bold rounded-xl hover:bg-[#13493E] transition-all duration-300 whitespace-nowrap cursor-pointer" style="font-family: Inter, sans-serif; font-weight: 700;">
                        {{ __('user.update_information') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Account Status Card -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h3 class="text-2xl font-bold text-[#1A1A1A] mb-6" style="font-family: Inter, sans-serif;">{{ __('user.account_status') }}</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Account Status -->
                <div class="bg-[#F5F7F8] rounded-xl p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($user->is_active)
                                <div class="w-12 h-12 bg-[#1E6F5C] rounded-xl flex items-center justify-center">
                                    <i class="ri-check-line text-white text-xl"></i>
                                </div>
                            @else
                                <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center">
                                    <i class="ri-close-line text-white text-xl"></i>
                                </div>
                            @endif
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-semibold text-[#1A1A1A]" style="font-family: Inter, sans-serif;">
                                {{ __('user.account_status') }}:
                                @if($user->is_active)
                                    <span class="text-[#1E6F5C] font-bold">{{ __('user.active') }}</span>
                                @else
                                    <span class="text-red-600 font-bold">{{ __('user.inactive') }}</span>
                                @endif
                            </p>
                            <p class="text-sm text-[#666666]" style="font-family: Inter, sans-serif;">
                                @if($user->isAdmin())
                                    {{ __('user.admin_account') }}
                                @else
                                    {{ __('user.user_account') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Membership Info -->
                <div class="bg-[#F5F7F8] rounded-xl p-6">
                    <h4 class="text-sm font-semibold text-[#1A1A1A] mb-4" style="font-family: Inter, sans-serif;">{{ __('user.membership_info') }}</h4>
                    <div class="space-y-3 text-sm text-[#666666]" style="font-family: Inter, sans-serif;">
                        <p class="flex items-center">
                            <i class="ri-calendar-line mr-2 text-[#1E6F5C]"></i>
                            <span class="font-medium">{{ __('user.membership') }}:</span>
                            <span class="ml-2">{{ $user->created_at->format('d.m.Y') }}</span>
                        </p>
                        <p class="flex items-center">
                            <i class="ri-history-line mr-2 text-[#1E6F5C]"></i>
                            <span class="font-medium">{{ __('user.last_update') }}:</span>
                            <span class="ml-2">{{ $user->updated_at->format('d.m.Y') }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Change Card -->
        <div class="bg-white shadow-sm rounded-xl border overflow-hidden">
            <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                <h3 class="text-xl font-semibold text-white flex items-center">
                    <i class="ri-lock-password-line mr-2"></i>
                    {{ __('user.password_security') }}
                </h3>
            </div>

            <div class="p-6">
                <form action="#" method="POST" id="passwordForm">
                    @csrf

                    <div class="space-y-6">
                        <!-- Current Password -->
                        <div class="group">
                            <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="ri-key-line mr-2 text-red-500"></i>{{ __('user.current_password') }}
                            </label>
                            <input type="password" name="current_password" id="current_password"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                                   required placeholder="{{ __('user.current_password_placeholder') }}">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- New Password -->
                            <div class="group">
                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="ri-lock-line mr-2 text-red-500"></i>{{ __('user.new_password') }}
                                </label>
                                <input type="password" name="password" id="password"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                                       required minlength="8" placeholder="{{ __('user.new_password_placeholder') }}">
                                <p class="mt-2 text-sm text-gray-500 flex items-center">
                                    <i class="ri-information-line mr-1"></i>
                                    {{ __('user.password_min_length') }}
                                </p>
                            </div>

                            <!-- Confirm New Password -->
                            <div class="group">
                                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="ri-lock-unlock-line mr-2 text-red-500"></i>{{ __('user.confirm_password') }}
                                </label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                                       required placeholder="{{ __('user.confirm_password_placeholder') }}">
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transform hover:scale-105 transition-all duration-200">
                            <i class="ri-key-line mr-2"></i>
                            {{ __('user.change_password') }}
                        </button>
```
                    </div>
                </form>
            </div>
        </div>


        <!-- Profile Tips Card -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl border border-blue-200 p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                <i class="ri-lightbulb-line mr-2"></i>
                💡 {{ __('user.profile_tips') }}
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h4 class="font-medium text-gray-900 mb-2 flex items-center">
                        <i class="ri-shield-check-line mr-2 text-green-500"></i>
                        {{ __('user.security') }}
                    </h4>
                    <p class="text-sm text-gray-600">{{ __('user.security_tip') }}</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h4 class="font-medium text-gray-900 mb-2 flex items-center">
                        <i class="ri-phone-line mr-2 text-blue-500"></i>
                        {{ __('user.contact') }}
                    </h4>
                    <p class="text-sm text-gray-600">{{ __('user.contact_tip') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/imask"></script>
<script>
// Phone input masking
const phoneInput = document.getElementById('phone');
if (phoneInput) {
    const phoneMask = IMask(phoneInput, {
        mask: '+90 000 000 00 00',
        lazy: false,
        placeholderChar: '_'
    });

    // Set initial value if exists
    if (phoneInput.value) {
        phoneMask.value = phoneInput.value;
    }
}
</script>
<script>
// Enhanced password confirmation validation
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;

    if (password !== confirmation) {
        e.preventDefault();

        // Show a better error message
        const confirmField = document.getElementById('password_confirmation');
        confirmField.classList.add('border-red-300', 'ring-red-300');

        // Create or update error message
        let errorMsg = confirmField.parentNode.querySelector('.error-message');
        if (!errorMsg) {
            errorMsg = document.createElement('p');
            errorMsg.className = 'mt-2 text-sm text-red-600 error-message flex items-center';
            errorMsg.innerHTML = '<i class="ri-error-warning-line mr-1"></i>{{ __("user.password_mismatch") }}';
            confirmField.parentNode.appendChild(errorMsg);
        }

        return false;
    }
});

// Remove error styling when user starts typing
document.getElementById('password_confirmation').addEventListener('input', function() {
    this.classList.remove('border-red-300', 'ring-red-300');
    const errorMsg = this.parentNode.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.remove();
    }
});

// Form submission loading state
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line mr-2 animate-spin"></i>{{ __("user.updating") }}';
        }
    });
});
</script>
@endpush

@push('styles')
<style>
/* Enhanced input focus states */
.group input:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Smooth animations */
.group {
    transition: all 0.2s ease;
}

.group:hover {
    transform: translateY(-1px);
}

/* Enhanced button animations */
button {
    transition: all 0.2s ease;
}

/* Pulse animation for active status */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
@endpush
@endsection
