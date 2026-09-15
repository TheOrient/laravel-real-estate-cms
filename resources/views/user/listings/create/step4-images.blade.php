@extends('layouts.app')

@section('title', __('listings.step4_title'))

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('listings.create_listing') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('listings.step4_images') }}</p>
        </div>

        <!-- Progress Steps -->
        <x-listing-create-progress :current-step="4" />
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto mt-10">
    <form action="{{ route('user.listings.create.step4.store', $listing) }}" method="POST" enctype="multipart/form-data" id="imagesForm">
        @csrf

        <!-- Image Manager Component -->
        <x-listing-image-manager
            :listing="$listing"
            mode="create"
            :max-images="10"
            :required="false" />

        {{-- Optional video — link or upload, both supported. --}}
        <div class="bg-white shadow rounded-lg p-6 mt-6">
            <h3 class="text-lg font-bold text-[#1A1A1A] mb-4 flex items-center">
                <i class="ri-vidicon-line mr-2 text-[#1E6F5C]"></i>{{ __('listings.video') }}
                <span class="ml-2 text-xs font-normal text-gray-400">({{ __('listings.optional') }})</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="video_url" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                        {{ __('listings.video_url') }}
                    </label>
                    <input type="url" name="video_url" id="video_url"
                           value="{{ old('video_url', $listing->video_url) }}"
                           placeholder="https://youtu.be/..."
                           class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1E6F5C] @error('video_url') border-red-500 @enderror">
                    <small class="text-gray-500">{{ __('listings.video_url_help') }}</small>
                    @error('video_url')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="video_file" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                        {{ __('listings.video_file') }}
                    </label>
                    <input type="file" name="video_file" id="video_file"
                           accept="video/mp4,video/webm,video/ogg"
                           class="block w-full text-sm">
                    <small class="text-gray-500">{{ __('listings.video_file_help', ['max' => (int) ceil(\App\Services\VideoEmbedService::maxFileBytes() / 1024 / 1024)]) }}</small>
                    @error('video_file')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-6 flex justify-between">
            <a href="{{ route('user.listings.my') }}"
               class="inline-flex items-center px-6 py-3 border border-[#E0E0E0] text-sm font-bold rounded-xl text-[#1A1A1A] bg-white hover:bg-[#F5F7F8] transition-all duration-300 whitespace-nowrap cursor-pointer" style="font-family: Inter, sans-serif; font-weight: 700;">
                <i class="ri-close-line mr-2"></i>
                {{ __('listings.skip_for_now') }}
            </a>

            <button type="submit"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl text-white bg-[#1E6F5C] hover:bg-[#13493E] transition-all duration-300 whitespace-nowrap cursor-pointer" style="font-family: Inter, sans-serif; font-weight: 700;">
                <i class="ri-check-line mr-2"></i>
                {{ __('listings.complete_listing') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Form submission handler
document.getElementById('imagesForm').addEventListener('submit', function(e) {
    const imageIdsInput = document.getElementById('imageIds');
    const imageIds = imageIdsInput ? imageIdsInput.value : '';

    // Allow submission even without images
    if (!imageIds || imageIds.trim() === '') {
        // Show confirmation if no images uploaded
        if (!confirm('{{ __('listings.no_photos_confirmation') }}')) {
            e.preventDefault();
            return false;
        }
    }

    return true;
});
</script>
@endpush
@endsection
