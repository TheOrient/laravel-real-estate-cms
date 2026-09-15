@extends('layouts.app')

@section('title', __('general.listing_photos'))

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('general.listing_photos') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $listing->title }}</p>
        </div>
        <a href="{{ route('user.listings.my') }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
            <i class="ri-arrow-left-line mr-2"></i>
            {{ __('general.my_listings') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto mt-10">
        <!-- Success Message -->
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6 mb-8 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="ri-checkbox-circle-line text-green-500 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-green-800">{{ __('general.listing_created_successfully') }}</h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>{{ __('general.listing_created_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Images -->
        @if ($listing->images->count() > 0)
            <div class="bg-white shadow-xl rounded-2xl p-6 mb-8 border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="ri-image-2-line text-primary mr-3"></i>
                        {{ __('general.current_photos') }}
                    </h3>
                    <span class="bg-[#1E6F5C]/10 text-primary text-sm font-medium px-3 py-1 rounded-full">
                        {{ $listing->images->count() }}/10
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($listing->images as $image)
                        <div class="relative group transform transition-all duration-200 hover:scale-105">
                            <div class="aspect-square overflow-hidden rounded-xl shadow-md">
                                <img loading="lazy" decoding="async" src="{{ listing_image_url($image->image, 'medium', 'crop') }}"
                                    alt="{{ __('general.listing_photo') }}"
                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                            </div>

                            @if ($image->is_primary)
                                <div class="absolute -top-2 -right-2 z-10">
                                    <span
                                        class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg flex items-center">
                                        <i class="ri-vip-crown-2-line mr-1"></i>
                                        {{ __('general.main_photo') }}
                                    </span>
                                </div>
                            @endif

                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl flex items-end justify-center">
                                <button type="button" onclick="deleteImage({{ $image->id }})"
                                    class="mb-3 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-lg transform transition-all duration-200 hover:scale-105 flex items-center">
                                    <i class="ri-delete-bin-line mr-2"></i>
                                    {{ __('general.delete') }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Upload Form -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
            <div class="bg-gradient-to-r from-[#1E6F5C]/10 to-[#1E6F5C]/20 px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                    <i class="ri-camera-line text-primary mr-3"></i>
                    {{ __('general.add_photos') }}
                </h3>
                <p class="text-sm text-gray-600 mt-1">{{ __('general.drag_drop_or_click') }}</p>
            </div>

            <form action="{{ route('user.listings.images.store', $listing) }}" method="POST" enctype="multipart/form-data"
                id="imageUploadForm">
                @csrf

                <div class="p-6">
                    <!-- Main Drop Zone -->
                    <div id="dropZone" class="relative group">
                        <input id="images" name="images[]" type="file"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" multiple accept="image/*">

                        <div class="border-3 border-dashed border-gray-300 rounded-2xl p-12 text-center transition-all duration-300 hover:border-primary hover:bg-[#1E6F5C]/10 group-hover:scale-[1.02]"
                            id="dropZoneContent">
                            <!-- Upload Icon -->
                            <div class="mb-6">
                                <div
                                    class="mx-auto w-20 h-20 bg-gradient-to-br from-[#1E6F5C]/10 to-[#1E6F5C]/20 rounded-full flex items-center justify-center transform transition-transform duration-300 group-hover:scale-110">
                                    <i class="ri-upload-cloud-2-line text-primary text-3xl"></i>
                                </div>
                            </div>

                            <!-- Upload Text -->
                            <div class="space-y-2">
                                <h4 class="text-xl font-semibold text-gray-900">{{ __('general.choose_photos') }}</h4>
                                <p class="text-gray-600">{{ __('general.or_drag_here') }}</p>

                                <!-- File Info -->
                                <div class="flex flex-wrap justify-center gap-4 mt-4">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-[#1E6F5C]/10 text-primary">
                                        <i class="ri-image-line mr-1"></i>
                                        JPG, PNG, GIF
                                    </span>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="ri-scales-3-line mr-1"></i>
                                        {{ __('general.max_2mb') }}
                                    </span>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <i class="ri-stack-line mr-1"></i>
                                        {{ __('general.max_10_photos') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Progress Overlay -->
                        <div id="uploadProgress"
                            class="hidden absolute inset-0 bg-white/95 rounded-2xl flex items-center justify-center z-30">
                            <div class="text-center">
                                <div class="w-16 h-16 mx-auto mb-4">
                                    <div
                                        class="animate-spin rounded-full h-16 w-16 border-4 border-primary border-t-transparent">
                                    </div>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ __('general.uploading') }}</h4>
                                <div class="w-64 bg-gray-200 rounded-full h-3 mx-auto">
                                    <div id="progressBar"
                                        class="bg-gradient-to-r from-[#1E6F5C] to-[#13493E] h-3 rounded-full transition-all duration-300"
                                        style="width: 0%"></div>
                                </div>
                                <p id="progressText" class="text-sm text-gray-600 mt-2">0%</p>
                            </div>
                        </div>
                    </div>

                    @error('images')
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-600 flex items-center">
                                <i class="ri-error-warning-line mr-2"></i>
                                {{ $message }}
                            </p>
                        </div>
                    @enderror
                    @error('images.*')
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-600 flex items-center">
                                <i class="ri-error-warning-line mr-2"></i>
                                {{ $message }}
                            </p>
                        </div>
                    @enderror
                </div>

                <!-- Preview Area -->
                <div id="imagePreview" class="hidden">
                    <div class="px-5 border-t border-gray-200 pt-6 pb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="ri-eye-line text-indigo-500 mr-2"></i>
                                {{ __('general.selected_photos') }}
                            </h4>
                            <button type="button" id="clearAll"
                                class="text-sm text-red-600 hover:text-red-800 font-medium flex items-center">
                                <i class="ri-close-line mr-1"></i>
                                {{ __('general.clear_all') }}
                            </button>
                        </div>
                        <div id="previewGrid" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5"></div>
                    </div>
                </div>

            </form>

            <!-- Upload Tips -->
            <div class="bg-gradient-to-r from-[#1E6F5C]/10 via-[#1E6F5C]/15 to-[#1E6F5C]/20 border-t border-gray-200 p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-[#1E6F5C]/10 rounded-full flex items-center justify-center">
                            <i class="ri-lightbulb-line text-primary"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('general.photo_tips') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center text-sm text-gray-700">
                                <i class="ri-sun-line text-yellow-500 mr-3 w-4"></i>
                                {{ __('general.tip_lighting') }}
                            </div>
                            <div class="flex items-center text-sm text-gray-700">
                                <i class="ri-camera-line text-primary mr-3 w-4"></i>
                                {{ __('general.tip_angles') }}
                            </div>
                            <div class="flex items-center text-sm text-gray-700">
                                <i class="ri-vip-crown-2-line text-orange-500 mr-3 w-4"></i>
                                {{ __('general.tip_first_main') }}
                            </div>
                            <div class="flex items-center text-sm text-gray-700">
                                <i class="ri-image-2-line text-purple-500 mr-3 w-4"></i>
                                {{ __('general.tip_max_count') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
                <a href="{{ route('user.listings.my') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    {{ __('general.skip_for_now') }}
                </a>

                <button type="submit" form="imageUploadForm"
                    class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-[#1E6F5C] to-[#13493E] hover:from-[#13493E] hover:to-[#0F1F1A] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary shadow-lg transform transition-all duration-200 hover:scale-105">
                    <i class="ri-upload-cloud-2-line mr-2"></i>
                    {{ __('general.upload_photos') }}
                </button>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        class ModernImageUploader {
            constructor() {
                this.selectedFiles = [];
                this.maxFiles = 10;
                this.maxFileSize = 2 * 1024 * 1024; // 2MB
                this.allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                this.initializeElements();
                this.bindEvents();
            }

            initializeElements() {
                this.fileInput = document.getElementById('images');
                this.dropZone = document.getElementById('dropZone');
                this.dropZoneContent = document.getElementById('dropZoneContent');
                this.previewContainer = document.getElementById('imagePreview');
                this.previewGrid = document.getElementById('previewGrid');
                this.uploadForm = document.getElementById('imageUploadForm');
                this.uploadProgress = document.getElementById('uploadProgress');
                this.progressBar = document.getElementById('progressBar');
                this.progressText = document.getElementById('progressText');
                this.clearAllBtn = document.getElementById('clearAll');
            }

            bindEvents() {
                // File input change
                this.fileInput.addEventListener('change', (e) => this.handleFileSelect(e));

                // Drag and drop events
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    this.dropZone.addEventListener(eventName, (e) => this.preventDefaults(e));
                    document.body.addEventListener(eventName, (e) => this.preventDefaults(e));
                });

                ['dragenter', 'dragover'].forEach(eventName => {
                    this.dropZone.addEventListener(eventName, () => this.highlight());
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    this.dropZone.addEventListener(eventName, () => this.unhighlight());
                });

                this.dropZone.addEventListener('drop', (e) => this.handleDrop(e));

                // Clear all button
                this.clearAllBtn.addEventListener('click', () => this.clearAllFiles());

                // Form submission
                this.uploadForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
            }

            preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            highlight() {
                this.dropZoneContent.classList.add('border-primary', 'bg-[#1E6F5C]/10', 'scale-105');
                this.dropZoneContent.classList.remove('border-gray-300');
            }

            unhighlight() {
                this.dropZoneContent.classList.remove('border-primary', 'bg-[#1E6F5C]/10', 'scale-105');
                this.dropZoneContent.classList.add('border-gray-300');
            }

            handleFileSelect(e) {
                this.processFiles(Array.from(e.target.files));
            }

            handleDrop(e) {
                const files = Array.from(e.dataTransfer.files);
                this.processFiles(files);
            }

            processFiles(files) {
                // Filter valid image files
                const validFiles = files.filter(file => {
                    if (!this.allowedTypes.includes(file.type)) {
                        this.showError(`"${file.name}" geçerli bir resim dosyası değil.`);
                        return false;
                    }
                    if (file.size > this.maxFileSize) {
                        this.showError(`"${file.name}" çok büyük (maksimum 2MB).`);
                        return false;
                    }
                    return true;
                });

                // Check total file count
                if (this.selectedFiles.length + validFiles.length > this.maxFiles) {
                    this.showError(`Maksimum ${this.maxFiles} fotoğraf yükleyebilirsiniz.`);
                    return;
                }

                // Add files to selection
                validFiles.forEach(file => {
                    const fileId = Date.now() + Math.random();
                    this.selectedFiles.push({
                        id: fileId,
                        file: file,
                        preview: null
                    });
                });

                this.updateFileInput();
                this.renderPreviews();
            }

            updateFileInput() {
                // Create new FileList
                const dt = new DataTransfer();
                this.selectedFiles.forEach(item => dt.items.add(item.file));
                this.fileInput.files = dt.files;
            }

            renderPreviews() {
                if (this.selectedFiles.length === 0) {
                    this.previewContainer.classList.add('hidden');
                    return;
                }

                this.previewContainer.classList.remove('hidden');
                this.previewGrid.innerHTML = '';

                this.selectedFiles.forEach((item, index) => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const div = document.createElement('div');
                        div.className =
                            'relative group transform transition-all duration-200 hover:scale-105';
                        div.innerHTML = `
                    <div class="aspect-square overflow-hidden rounded-xl shadow-lg">
                        <img src="${e.target.result}"
                             alt="Preview ${index + 1}"
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                    </div>

                    <!-- File Info -->
                    <div class="absolute top-2 left-2 z-10">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-[#1E6F5C] to-[#13493E] text-white shadow-lg">
                            ${index + 1}
                        </span>
                    </div>

                    <!-- File Size -->
                    <div class="absolute top-2 right-2 z-10">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-black/70 text-white">
                            ${this.formatFileSize(item.file.size)}
                        </span>
                    </div>

                    <!-- Remove Button -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl flex items-end justify-center">
                        <button type="button"
                                class="mb-3 bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg text-sm font-medium shadow-lg transform transition-all duration-200 hover:scale-105 flex items-center remove-file"
                                data-file-id="${item.id}">
                            <i class="ri-delete-bin-line mr-1"></i>
                            {{ __('general.remove') }}
                        </button>
                    </div>
                `;
                        this.previewGrid.appendChild(div);

                        // Add remove functionality
                        const removeBtn = div.querySelector('.remove-file');
                        removeBtn.addEventListener('click', () => this.removeFile(item.id));
                    };
                    reader.readAsDataURL(item.file);
                });
            }

            removeFile(fileId) {
                this.selectedFiles = this.selectedFiles.filter(item => item.id !== fileId);
                this.updateFileInput();
                this.renderPreviews();
            }

            clearAllFiles() {
                this.selectedFiles = [];
                this.updateFileInput();
                this.renderPreviews();
            }

            formatFileSize(bytes) {
                if (bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
            }

            showError(message) {
                // Create toast notification
                const toast = document.createElement('div');
                toast.className =
                    'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
                toast.innerHTML = `
            <div class="flex items-center">
                <i class="ri-error-warning-line mr-2"></i>
                <span>${message}</span>
            </div>
        `;

                document.body.appendChild(toast);

                // Animate in
                setTimeout(() => {
                    toast.classList.remove('translate-x-full');
                }, 100);

                // Remove after 4 seconds
                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            }

            showProgress(percent) {
                this.uploadProgress.classList.remove('hidden');
                this.progressBar.style.width = percent + '%';
                this.progressText.textContent = percent + '%';
            }

            hideProgress() {
                this.uploadProgress.classList.add('hidden');
            }

            handleFormSubmit(e) {
                if (this.selectedFiles.length === 0) {
                    e.preventDefault();
                    this.showError('{{ __('general.select_at_least_one') }}');
                    return false;
                }

                e.preventDefault(); // Prevent regular form submission
                this.uploadViaAjax();
            }

            async uploadViaAjax() {
                try {
                    // Show progress
                    this.showProgress(0);

                    // Prepare FormData
                    const formData = new FormData();
                    this.selectedFiles.forEach(item => {
                        formData.append('images[]', item.file);
                    });
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content'));

                    // Upload with XMLHttpRequest for progress tracking
                    const xhr = new XMLHttpRequest();

                    // Track upload progress
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const percentComplete = Math.round((e.loaded / e.total) * 100);
                            this.showProgress(percentComplete);
                        }
                    });

                    // Handle response
                    xhr.addEventListener('load', () => {
                        try {
                            const response = JSON.parse(xhr.responseText);

                            if (xhr.status === 200 && response.success) {
                                this.showSuccess(response.message);

                                // Clear selected files
                                this.clearAllFiles();

                                // Redirect after success
                                setTimeout(() => {
                                    window.location.href = '{{ route('user.listings.my') }}';
                                }, 2000);

                            } else {
                                this.hideProgress();
                                this.showError(response.message || 'Yükleme sırasında hata oluştu.');
                            }
                        } catch (error) {
                            this.hideProgress();
                            this.showError('Sunucu yanıtı işlenirken hata oluştu.');
                        }
                    });

                    // Handle errors
                    xhr.addEventListener('error', () => {
                        this.hideProgress();
                        this.showError('Yükleme sırasında ağ hatası oluştu.');
                    });

                    // Send request
                    xhr.open('POST', '{{ route('user.listings.images.store.ajax', $listing) }}');
                    xhr.send(formData);

                } catch (error) {
                    this.hideProgress();
                    this.showError('Beklenmeyen hata: ' + error.message);
                }
            }

            showSuccess(message) {
                // Create success toast notification
                const toast = document.createElement('div');
                toast.className =
                    'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full toast-enter';
                toast.innerHTML = `
            <div class="flex items-center">
                <i class="ri-checkbox-circle-line mr-2"></i>
                <span>${message}</span>
            </div>
        `;

                document.body.appendChild(toast);

                // Animate in
                setTimeout(() => {
                    toast.classList.remove('translate-x-full');
                }, 100);

                // Remove after 4 seconds
                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            }
        }

        // Delete existing image function
        function deleteImage(imageId) {
            if (confirm('{{ __('general.confirm_delete_photo') }}')) {
                // Show loading state
                const button = event.target.closest('button');
                const originalContent = button.innerHTML;
                button.innerHTML = '<i class="ri-loader-4-line animate-spin mr-2"></i>{{ __('general.deleting') }}';
                button.disabled = true;

                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/panel/listing-images/${imageId}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }

        var uploader = null;
        // Initialize uploader when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            uploader = new ModernImageUploader();
        });

    </script>
@endpush
