@extends('layouts.app')

@section('title', __('auth.forgot_password_title'))
@section('robots', 'noindex, nofollow')

@section('content')
    <div class="flex-1 flex items-center justify-center px-6 py-32">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-[#1A1A1A] mb-2" style="font-family: Inter, sans-serif;">{{ __('auth.forgot_password_title') }}</h2>
                <p class="text-sm text-[#666666]" style="font-family: Inter, sans-serif;">{{ __('auth.forgot_password_subtitle') }}</p>
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

            @if (session('status'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg mb-6">
                    <div class="flex items-start">
                        <i class="ri-checkbox-circle-line text-green-500 text-xl mr-3 mt-0.5"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-green-800">{{ session('status') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-lg p-8">
                <form class="space-y-6" action="{{ route('password.email') }}" method="POST">
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

                    <button type="submit"
                            class="w-full px-6 py-3 bg-[#1E6F5C] text-white text-sm font-bold rounded-xl hover:bg-[#13493E] transition-all duration-300 whitespace-nowrap cursor-pointer"
                            style="font-family: Inter, sans-serif; font-weight: 700;">{{ __('auth.send_reset_link') }}</button>
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}"
                       class="text-sm text-[#1E6F5C] hover:text-[#13493E] font-semibold transition-colors cursor-pointer"
                       style="font-family: Inter, sans-serif;">{{ __('auth.back_to_login') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
