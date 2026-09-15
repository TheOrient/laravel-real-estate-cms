@extends('admin.layouts.app')

@section('title', 'Kategori Özellikleri')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Kategori Özellikleri</h1>
        <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">
            <i class="ri-arrow-left-line"></i> Özelliklere Dön
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Kategoriler</h5>
                </div>
                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                    @if($categories->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($categories as $category)
                                <div class="list-group-item p-3 category-item" data-category-id="{{ $category->id }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $category->name }}</h6>
                                            @if($category->parent)
                                                <small class="text-muted">
                                                    Alt kategori: {{ $category->parent->name }}
                                                </small>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="badge badge-primary">
                                                {{ $category->attributes->count() }} özellik
                                            </span>
                                            <button class="btn btn-sm btn-outline-primary select-category"
                                                    data-category-id="{{ $category->id }}"
                                                    data-category-name="{{ $category->name }}">
                                                Seç
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center">
                            <i class="ri-folder-line text-muted" style="font-size: 48px;"></i>
                            <h6 class="mt-3">Kategori bulunamadı</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card" id="attributeAssignCard" style="display: none;">
                <div class="card-header">
                    <h5 class="mb-0">
                        <span id="selectedCategoryName"></span> - Özellik Atama
                    </h5>
                </div>
                <div class="card-body">
                    <form id="assignAttributesForm" method="POST">
                        @csrf
                        <input type="hidden" id="selectedCategoryId" name="category_id">

                        <div class="row">
                            <div class="col-md-6">
                                <h6>Mevcut Özellikler</h6>
                                <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                    @if($attributes->count() > 0)
                                        @foreach($attributes as $attribute)
                                            <div class="form-check mb-3 border-bottom pb-3">
                                                <input type="checkbox"
                                                       class="form-check-input attribute-checkbox"
                                                       id="attr_{{ $attribute->id }}"
                                                       name="attributes[]"
                                                       value="{{ $attribute->id }}">
                                                <label class="form-check-label" for="attr_{{ $attribute->id }}">
                                                    <strong>{{ $attribute->name }}</strong>
                                                    <small class="text-muted"> Arama görünümü:
                                                        {{ $attribute->display_type_search }}
                                                        @if($attribute->is_required)
                                                            | Zorunlu
                                                        @endif
                                                        @if($attribute->is_filterable)
                                                            | Filtrelenebilir
                                                        @endif
                                                        | İlan ekleme görünümü:
                                                        {{$attribute->display_type_user_panel}}
                                                    </small>
                                                </label>

                                                <!-- Position selection -->
                                                <div class="mt-2 position-selection" id="position_{{ $attribute->id }}" style="display: none;">
                                                    <small class="text-muted d-block mb-1">İlan detayında gösterim pozisyonu:</small>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input position-radio" type="radio"
                                                               id="pos_top_{{ $attribute->id }}"
                                                               name="positions[{{ $attribute->id }}]"
                                                               value="top">
                                                        <label class="form-check-label" for="pos_top_{{ $attribute->id }}">
                                                            <small>Üstte</small>
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input position-radio" type="radio"
                                                               id="pos_bottom_{{ $attribute->id }}"
                                                               name="positions[{{ $attribute->id }}]"
                                                               value="bottom"
                                                               checked>
                                                        <label class="form-check-label" for="pos_bottom_{{ $attribute->id }}">
                                                            <small>Altta</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted mb-0">Henüz özellik eklenmemiş.</p>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6>Bu Kategoriye Atanmış Özellikler</h6>
                                <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                    <div id="assignedAttributes">
                                        <!-- Assigned attributes will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> Özellikleri Kaydet
                            </button>
                            <button type="button" class="btn btn-secondary" id="clearSelection">
                                Seçimi Temizle
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card" id="selectCategoryCard">
                <div class="card-body text-center py-5">
                    <i class="ri-cursor-line text-muted" style="font-size: 48px;"></i>
                    <h5 class="mt-3">Kategori Seçin</h5>
                    <p class="text-muted">Özellik atamak için soldaki listeden bir kategori seçin.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const attributeAssignCard = document.getElementById('attributeAssignCard');
    const selectCategoryCard = document.getElementById('selectCategoryCard');
    const selectedCategoryName = document.getElementById('selectedCategoryName');
    const selectedCategoryId = document.getElementById('selectedCategoryId');
    const assignedAttributes = document.getElementById('assignedAttributes');
    const assignAttributesForm = document.getElementById('assignAttributesForm');
    const clearSelectionBtn = document.getElementById('clearSelection');

    // Category selection
    document.querySelectorAll('.select-category').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            const categoryName = this.dataset.categoryName;

            // Update UI
            selectedCategoryName.textContent = categoryName;
            selectedCategoryId.value = categoryId;
            selectCategoryCard.style.display = 'none';
            attributeAssignCard.style.display = 'block';

            // Update form action
            assignAttributesForm.action = `/admin/categories/${categoryId}/assign-attributes`;

            // Highlight selected category
            document.querySelectorAll('.category-item').forEach(item => {
                item.classList.remove('bg-light');
            });
            document.querySelector(`.category-item[data-category-id="${categoryId}"]`).classList.add('bg-light');

            // Load category attributes
            loadCategoryAttributes(categoryId);
        });
    });

    // Clear selection
    clearSelectionBtn.addEventListener('click', function() {
        document.querySelectorAll('.attribute-checkbox').forEach(checkbox => {
            checkbox.checked = false;
            // Hide position selection
            document.getElementById('position_' + checkbox.value).style.display = 'none';
        });
    });

    // Handle attribute checkbox change
    document.querySelectorAll('.attribute-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const positionDiv = document.getElementById('position_' + this.value);
            if (this.checked) {
                positionDiv.style.display = 'block';
            } else {
                positionDiv.style.display = 'none';
            }
        });
    });

    // Form submission
    assignAttributesForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('success', 'Özellikler başarıyla atandı!');

                // Reload category attributes
                loadCategoryAttributes(selectedCategoryId.value);

                // Update category attribute count
                const categoryId = selectedCategoryId.value;
                const categoryItem = document.querySelector(`.category-item[data-category-id="${categoryId}"]`);
                const checkedAttributes = document.querySelectorAll('.attribute-checkbox:checked').length;
                const badge = categoryItem.querySelector('.badge');
                badge.textContent = `${checkedAttributes} özellik`;
            } else {
                showAlert('error', 'Bir hata oluştu: ' + (data.message || 'Bilinmeyen hata'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Bir hata oluştu. Lütfen tekrar deneyin.');
        });
    });

    function loadCategoryAttributes(categoryId) {
        fetch(`/admin/categories/${categoryId}/attributes`)
            .then(response => response.json())
            .then(attributes => {
                // Clear all checkboxes and hide position selections
                document.querySelectorAll('.attribute-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                    document.getElementById('position_' + checkbox.value).style.display = 'none';
                });

                // Check assigned attributes and set positions
                attributes.forEach(attribute => {
                    const checkbox = document.getElementById(`attr_${attribute.id}`);
                    if (checkbox) {
                        checkbox.checked = true;
                        // Show position selection
                        document.getElementById('position_' + attribute.id).style.display = 'block';

                        // Set position radio button
                        const position = attribute.pivot?.property_detail_position || 'bottom';
                        const positionRadio = document.getElementById(`pos_${position}_${attribute.id}`);
                        if (positionRadio) {
                            positionRadio.checked = true;
                        }
                    }
                });

                // Update assigned attributes display
                if (attributes.length > 0) {
                    assignedAttributes.innerHTML = attributes.map(attr => `
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <div>
                                <strong>${attr.name}</strong>
                                <br>
                                <small class="text-muted">${attr.input_type}</small>
                            </div>
                            <div>
                                ${attr.is_required ? '<span class="badge badge-danger badge-sm">Zorunlu</span>' : ''}
                                ${attr.is_filterable ? '<span class="badge badge-success badge-sm">Filtrelenebilir</span>' : ''}
                                <span class="badge badge-${attr.pivot?.property_detail_position === 'top' ? 'primary' : 'secondary'} badge-sm">
                                    ${attr.pivot?.property_detail_position === 'top' ? 'Üstte' : 'Altta'}
                                </span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    assignedAttributes.innerHTML = '<p class="text-muted mb-0">Bu kategoriye henüz özellik atanmamış.</p>';
                }
            })
            .catch(error => {
                console.error('Error loading category attributes:', error);
                assignedAttributes.innerHTML = '<p class="text-danger mb-0">Özellikler yüklenirken hata oluştu.</p>';
            });
    }

    function showAlert(type, message) {
        // Create alert element
        const alert = document.createElement('div');
        alert.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;

        // Insert at top of content
        const content = document.querySelector('.content');
        content.insertBefore(alert, content.firstChild);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
});
</script>
@endpush
@endsection
