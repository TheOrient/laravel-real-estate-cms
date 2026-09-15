@extends('layouts.app')

@section('title', __('auth.login_title'))
@section('robots', 'noindex, nofollow')

@section('content')
    {{-- Soft branded canvas — hafif yeşil radyal, arka planı tek-ofis
         kimliğine bağlar; login artık sayfa değil "bir yönetim geçidi"
         hissi verir. --}}
    <div class="flex-1 flex items-center justify-center px-6 py-24 lg:py-32 relative"
         style="background: radial-gradient(ellipse 60% 50% at 50% 0%, rgba(30,111,92,0.06), transparent 70%), #F5F7F8;">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <span class="inline-flex items-center gap-2 px-3 py-1 mb-4 rounded-full"
                      style="background: rgba(30,111,92,0.10); color: #1E6F5C; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; border: 1px solid rgba(30,111,92,0.18);">
                    <i class="ri-shield-user-line"></i> {{ __('auth.admin_area_pill') }}
                </span>
                <h2 class="text-3xl font-bold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif; letter-spacing: -0.01em;">{{ __('auth.login_welcome') }}</h2>
                <p class="text-sm text-[#666666]" style="font-family: Inter, sans-serif;">{{ __('auth.login_subtitle') }}</p>
            </div>

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg mb-6">
                    <div class="flex items-start">
                        <i class="ri-error-warning-line text-red-500 text-xl mr-3 mt-0.5"></i>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-red-800 mb-2">{{ __('auth.fix_errors') }}</h3>
                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-lg p-8">
                <form class="space-y-6" action="{{ route('login') }}" method="POST">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('auth.email') }}</label>
                        <input id="email" name="email" type="email" required
                            class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all @error('email') border-red-500 @enderror"
                            placeholder="{{ __('auth.email_placeholder') }}" value="{{ old('email') }}" style="font-family: Inter, sans-serif;">
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('auth.password_label') }}</label>
                        <input id="password" name="password" type="password" required
                            class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] focus:border-transparent transition-all"
                            placeholder="{{ __('auth.password_placeholder') }}" style="font-family: Inter, sans-serif;">
                        @error('password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer">
                            <input class="w-4 h-4 text-[#1E6F5C] border-[#E0E0E0] rounded focus:ring-[#1E6F5C] cursor-pointer"
                                   type="checkbox" name="remember" id="remember">
                            <span class="ml-2 text-sm text-[#666666]" style="font-family: Inter, sans-serif;">{{ __('auth.remember_me') }}</span>
                        </label>
                        <a href="{{ route('password.request') }}"
                           class="text-sm text-[#1E6F5C] hover:text-[#13493E] font-semibold transition-colors cursor-pointer"
                           style="font-family: Inter, sans-serif;">{{ __('auth.forgot_password') }}</a>
                    </div>

                    <button type="submit"
                            class="w-full px-6 py-3 bg-[#1E6F5C] text-white text-sm font-bold rounded-xl hover:bg-[#13493E] transition-all duration-300 whitespace-nowrap cursor-pointer"
                            style="font-family: Inter, sans-serif; font-weight: 700;">{{ __('auth.login_button') }}</button>
                </form>

            </div>
        </div>
    </div>
@endsection
