@extends('layouts.app')

@section('title', __('auth.verify_email_title'))
@section('robots', 'noindex, nofollow')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('auth.verify_email_title') }}</h1>
            <p class="text-gray-600">{{ __('auth.verify_email_subtitle') }}</p>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <p class="text-sm text-blue-700">
                {{ __('auth.verify_email_sent') }}
            </p>
        </div>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                {{ __('auth.resend_verification') }}
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                {{ __('auth.wrong_email') }}
                <a href="{{ route('user.profile') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">
                    {{ __('auth.change_email_in_profile') }}
                </a>
            </p>
        </div>

        <div class="mt-4 text-center">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                    {{ __('auth.logout') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
