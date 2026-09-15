{{--
  Listing Image Manager Component
  Used for both creating and editing listings

  Props:
  - $listing: Listing model (optional, null for create mode)
  - $mode: 'create' or 'edit'
  - $maxImages: Maximum number of images allowed (default: 10)
  - $required: Whether images are required (default: false for edit, true for create)
--}}

@props([
    'listing' => null,
    'mode' => 'create', // 'create' or 'edit'
    'maxImages' => 10,
    'required' => null
])

@php
    $isEdit = $mode === 'edit' && $listing !== null;
    $currentImages = $isEdit ? $listing->images : collect();
    $required = $required ?? ($mode === 'create'); // Required for create by default
@endphp

<div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900 flex items-center">
            <i class="ri-image-2-line text-blue-500 mr-3"></i>
            {{ __('general.listing_photos') }}
        </h3>
        <p class="text-sm text-gray-600 mt-1">
            @if($isEdit)
                {{ __('general.manage_photos_desc') }}
            @else
                {{ __('general.add_photos_desc') }}
            @endif
        </p>
    </div>

    <!-- Main Images Area -->
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="ri-image-2-line text-blue-500 mr-2"></i>
                {{ __('general.listing_photos') }}
            </h4>
            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full" id="imageCount">
                {{ $isEdit ? $currentImages->count() : 0 }}/{{ $maxImages }}
            </span>
        </div>

        <!-- Drop Zone -->
        <div id="imageDropZone" class="relative group mb-6">
            <input id="newImages"
                   type="file"
                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20"
                   multiple
                   accept="image/*">

            <div class="border-3 border-dashed border-gray-300 rounded-2xl p-8 text-center transition-all duration-300 hover:border-blue-400 hover:bg-blue-50/50 group-hover:scale-[1.02]" id="dropZoneContent">
                <!-- Upload Icon -->
                <div class="mb-4">
                    <div class="mx-auto w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center transform transition-transform duration-300 group-hover:scale-110">
                        <i class="ri-add-line text-blue-500 text-2xl"></i>
                    </div>
                </div>

                <!-- Upload Text -->
                <div class="space-y-2">
                    <h5 class="text-lg font-semibold text-gray-900">{{ __('general.choose_photos') }}</h5>
                    <p class="text-gray-600">{{ __('general.or_drag_here') }}</p>

                    <!-- File Info -->
                    <div class="flex flex-wrap justify-center gap-4 mt-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="ri-image-line mr-1"></i>
                            JPG, PNG, GIF
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="ri-scales-3-line mr-1"></i>
                            {{ __('general.max_2mb') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Images Grid (mevcut + yeni resimler hepsi burada görünecek) -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5" id="allImagesGrid" style="touch-action: none;">
            @if($isEdit)
                @foreach($currentImages as $image)
                    <div class="relative group transform transition-all duration-200 hover:scale-105" id="image-{{ $image->id }}" data-image-id="{{ $image->id }}">
                        <div class="aspect-square overflow-hidden rounded-xl shadow-md cursor-move">
                            <img loading="lazy" decoding="async" src="{{ listing_image_url($image->image, 'medium', 'crop') }}"
                                 alt="{{ __('general.listing_photo') }}"
                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                        </div>

                        @if($image->is_primary)
                            <div class="absolute -top-2 -right-2 z-10">
                                <span class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg flex items-center">
                                    <i class="ri-vip-crown-2-line mr-1"></i>
                                    {{ __('general.main_photo') }}
                                </span>
                            </div>
                        @endif

                        <!-- Drag Handle -->
                        <div class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="bg-black/70 text-white text-xs px-2 py-1 rounded-lg flex items-center">
                                <i class="ri-draggable mr-1"></i>
                                Sürükle
                            </span>
                        </div>

                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl flex items-end justify-center">
                            <button type="button"
                                    onclick="window.listingImageManager.deleteImage({{ $image->id }})"
                                    class="mb-3 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-lg transform transition-all duration-200 hover:scale-105 flex items-center">
                                <i class="ri-delete-bin-line mr-2"></i>
                                {{ __('general.delete') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Hidden input for image IDs (order matters) -->
        <input type="hidden" id="imageIds" name="image_ids" value="{{ $isEdit ? $currentImages->pluck('id')->implode(',') : '' }}">
    </div>

    <!-- Upload Tips -->
    <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 border-t border-gray-200 p-6">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="ri-lightbulb-line text-blue-500"></i>
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
                        <i class="ri-camera-line text-blue-500 mr-3 w-4"></i>
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
</div>

@push('scripts')
<script>
// Image Manager for Listing - with AJAX upload
class ListingImageManager {
    constructor(mode, listingId = null) {
        this.mode = mode; // 'create' or 'edit'
        this.listingId = listingId;
        this.maxFiles = {{ $maxImages }};
        this.maxFileSize = 2 * 1024 * 1024; // 2MB
        this.allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        this.uploadQueue = [];
        this.isUploading = false;

        // Upload URL (Blade'de direkt oluştur)
        @if($listing)
        this.uploadUrl = "{{ route('user.listings.images.upload.ajax', $listing) }}";
        @else
        this.uploadUrl = null; // Will be set later when listing is created
        @endif

        this.initializeElements();
        this.bindEvents();
        this.initSortable();
    }

    initializeElements() {
        this.fileInput = document.getElementById('newImages');
        this.dropZone = document.getElementById('imageDropZone');
        this.dropZoneContent = document.getElementById('dropZoneContent');
        this.imagesGrid = document.getElementById('allImagesGrid');
        this.imageIdsInput = document.getElementById('imageIds');
    }

    bindEvents() {
        // File input change - AJAX upload
        this.fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.handleFileSelect(e.target.files);
                // Reset input
                e.target.value = '';
            }
        });

        // Drag and drop events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, (e) => this.preventDefaults(e));
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, () => this.highlight());
        });

        ['dragleave', 'drop'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, () => this.unhighlight());
        });

        this.dropZone.addEventListener('drop', (e) => {
            const files = Array.from(e.dataTransfer.files);
            this.handleFileSelect(files);
        });
    }

    initSortable() {
        if (typeof Sortable === 'undefined') {
            console.warn('Sortable.js not loaded');
            return;
        }

        // Initialize SortableJS for all images grid
        this.sortableInstance = Sortable.create(this.imagesGrid, {
            animation: 200,
            easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            swapThreshold: 0.65,
            onEnd: (evt) => {
                console.log('Images reordered:', evt.oldIndex, '->', evt.newIndex);
                this.updateHiddenInput();
            }
        });
    }

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    highlight() {
        this.dropZoneContent.classList.add('border-blue-400', 'bg-blue-50', 'scale-105');
        this.dropZoneContent.classList.remove('border-gray-300');
    }

    unhighlight() {
        this.dropZoneContent.classList.remove('border-blue-400', 'bg-blue-50', 'scale-105');
        this.dropZoneContent.classList.add('border-gray-300');
    }

    handleFileSelect(files) {
        // Filter valid image files
        const validFiles = Array.from(files).filter(file => {
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

        if (validFiles.length === 0) return;

        // Check total ACTIVE + LOADING file count (exclude inactive/deleted ones)
        const currentImageCount = this.getCurrentImageCount();
        if (currentImageCount + validFiles.length > this.maxFiles) {
            this.showError(`Toplam maksimum ${this.maxFiles} fotoğraf yükleyebilirsiniz. (Şu anda: ${currentImageCount})`);
            return;
        }

        // Add to upload queue and start uploading
        validFiles.forEach(file => {
            const tempId = 'temp-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
            this.uploadQueue.push({ file, tempId });
            this.createLoadingPreview(file, tempId);
        });

        this.processUploadQueue();
    }

    createLoadingPreview(file, tempId) {
        const div = document.createElement('div');
        div.className = 'relative group transform transition-all duration-200';
        div.id = `image-${tempId}`;
        div.dataset.imageId = tempId;
        div.dataset.loading = 'true';

        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            div.innerHTML = `
                <div class="aspect-square overflow-hidden rounded-xl shadow-md relative">
                    <img src="${e.target.result}"
                         alt="Yükleniyor..."
                         class="w-full h-full object-cover opacity-30 blur-sm">

                    <!-- Loading Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/60 to-purple-500/60 flex items-center justify-center backdrop-blur-sm">
                        <div class="text-center text-white">
                            <!-- Modern Spinner -->
                            <div class="relative w-16 h-16 mx-auto mb-3">
                                <div class="absolute inset-0 border-4 border-white/30 rounded-full"></div>
                                <div class="absolute inset-0 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
                            </div>
                            <p class="text-sm font-medium animate-pulse">Yükleniyor...</p>
                        </div>
                    </div>
                </div>
            `;
        };
        reader.readAsDataURL(file);

        this.imagesGrid.appendChild(div);
        this.updateImageCount();
    }

    async processUploadQueue() {
        if (this.isUploading || this.uploadQueue.length === 0) return;

        this.isUploading = true;

        // Upload one by one (sıralı olarak)
        while (this.uploadQueue.length > 0) {
            const { file, tempId } = this.uploadQueue.shift();
            await this.uploadImage(file, tempId); // Bir önceki bitene kadar bekle
        }

        this.isUploading = false;
    }

    async uploadImage(file, tempId) {
        const formData = new FormData();
        formData.append('images[]', file);

        try {
            const response = await fetch(this.uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success && data.images && data.images.length > 0) {
                const uploadedImage = data.images[0];
                this.replaceLoadingWithImage(tempId, uploadedImage);
                this.showSuccess(`${file.name} başarıyla yüklendi`);
            } else {
                this.removeLoadingPreview(tempId);
                this.showError(data.message || 'Yükleme başarısız');
            }
        } catch (error) {
            console.error('Upload error:', error);
            this.removeLoadingPreview(tempId);
            this.showError(`${file.name} yüklenirken hata oluştu`);
        }
    }

    replaceLoadingWithImage(tempId, imageData) {
        const loadingDiv = document.getElementById(`image-${tempId}`);
        if (!loadingDiv) {
            console.warn('Loading div not found for tempId:', tempId);
            return;
        }

        // Create new image element
        const div = document.createElement('div');
        div.className = 'relative group transform transition-all duration-200 hover:scale-105';
        div.id = `image-${imageData.id}`;
        div.dataset.imageId = imageData.id; // Gerçek resim ID'si

        div.innerHTML = `
            <div class="aspect-square overflow-hidden rounded-xl shadow-md cursor-move">
                <img src="${imageData.url}"
                     alt="Listing photo"
                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
            </div>

            ${imageData.is_primary ? `
            <div class="absolute -top-2 -right-2 z-10">
                <span class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg flex items-center">
                    <i class="ri-vip-crown-2-line mr-1"></i>
                    Ana Fotoğraf
                </span>
            </div>
            ` : ''}

            <!-- Drag Handle -->
            <div class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                <span class="bg-black/70 text-white text-xs px-2 py-1 rounded-lg flex items-center">
                    <i class="ri-draggable mr-1"></i>
                    Sürükle
                </span>
            </div>

            <!-- Delete Button -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl flex items-end justify-center">
                <button type="button"
                        onclick="window.listingImageManager.deleteImage(${imageData.id})"
                        class="mb-3 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-lg transform transition-all duration-200 hover:scale-105 flex items-center">
                    <i class="ri-delete-bin-line mr-2"></i>
                    Sil
                </button>
            </div>
        `;

        // Replace loading preview with actual image
        loadingDiv.replaceWith(div);

        console.log('✓ Image uploaded and replaced. ID:', imageData.id);

        // IMPORTANT: Update hidden input with the new image ID
        this.updateHiddenInput();
        this.updateImageCount();
    }

    removeLoadingPreview(tempId) {
        const loadingDiv = document.getElementById(`image-${tempId}`);
        if (loadingDiv) {
            loadingDiv.remove();
            this.updateImageCount();
        }
    }

    deleteImage(imageId) {
        if (!confirm('Bu fotoğrafı silmek istediğinizden emin misiniz?')) {
            return;
        }

        const imageElement = document.getElementById(`image-${imageId}`);
        if (imageElement) {
            // Animasyonlu silme
            imageElement.style.opacity = '0';
            imageElement.style.transform = 'scale(0.8)';

            setTimeout(() => {
                imageElement.remove();
                this.updateHiddenInput();
                this.updateImageCount();
                console.log('Image deleted:', imageId);
            }, 300);
        }
    }

    updateHiddenInput() {
        // Get all image IDs in current order (exclude loading ones)
        const imageElements = this.imagesGrid.querySelectorAll('[data-image-id]:not([data-loading="true"])');
        const imageIds = Array.from(imageElements)
            .map(el => el.dataset.imageId)
            .filter(id => id && !id.startsWith('temp-')); // Sadece gerçek ID'leri al (temp ID'leri değil)

        this.imageIdsInput.value = imageIds.join(',');
        console.log('✓ Hidden input updated. Image IDs:', imageIds);
        console.log('✓ Hidden input value:', this.imageIdsInput.value);
    }

    getCurrentImageCount() {
        // Count only active images and loading images (exclude deleted/inactive ones)
        // This includes: existing active images + currently uploading images
        return this.imagesGrid.querySelectorAll('[data-image-id]').length;
    }

    updateImageCount() {
        const currentCount = this.getCurrentImageCount();
        const counterElement = document.getElementById('imageCount');
        if (counterElement) {
            counterElement.textContent = `${currentCount}/${this.maxFiles}`;
        }
    }

    showError(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="ri-error-warning-line mr-2"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);
        setTimeout(() => toast.classList.remove('translate-x-full'), 100);
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    showSuccess(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="ri-checkbox-circle-line mr-2"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);
        setTimeout(() => toast.classList.remove('translate-x-full'), 100);
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    const mode = '{{ $mode }}';
    const listingId = {{ $listing ? $listing->id : 'null' }};

    console.log('Initializing ListingImageManager with:', { mode, listingId });
    window.listingImageManager = new ListingImageManager(mode, listingId);

    if (window.listingImageManager) {
        console.log('✓ ListingImageManager initialized successfully');
        console.log('  - Mode:', window.listingImageManager.mode);
        console.log('  - Listing ID:', window.listingImageManager.listingId);
    }
});
</script>
@endpush
