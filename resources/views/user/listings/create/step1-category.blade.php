@extends('layouts.app')

@section('title', __('listings.create_step1_title'))

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('listings.create_listing') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('listings.step1_category') }}</p>
        </div>

        <!-- Progress Steps -->
        <x-listing-create-progress :current-step="1" />
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto mt-10">
        <form action="{{ route('user.listings.create.step1.store') }}" method="POST" id="categoryForm">
            @csrf

            <input type="hidden" name="category_id" id="selectedCategoryId" required>

            <div id="renderedSubcategorySection" class="bg-white shadow rounded-lg p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('listings.category_selection') }}</h3>
                    <p class="text-sm text-gray-600">{{ __('listings.category_selection_help') }}</p>
                </div>

                <!-- Category Selection -->
                <div class="subcategorySection grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categories as $category)
                        @php
                            $categoryDescription = $category->getRelation('description');
                        @endphp
                        <div class="subcategory-card relative border-2 border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors cursor-pointer"
                            onclick="selectCategory({{ $category->id }}, this.dataset.categoryName)"
                            data-category-name="{{ $categoryDescription?->name ?? $category->name }}"
                            data-category-id="{{ $category->id }}">

                            <div class="flex items-center mb-3">
                                @if ($category->icon)
                                    <i class="{{ $category->icon }} text-2xl text-blue-600 mr-3"></i>
                                @else
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="ri-folder-line text-blue-600"></i>
                                    </div>
                                @endif
                                <h4 class="text-lg font-medium text-gray-900">{{ $categoryDescription?->name ?? $category->name }}</h4>
                            </div>

                            @if ($categoryDescription?->short_description)
                                <p class="text-sm text-gray-600 mb-3">{{ $categoryDescription->short_description }}</p>
                            @endif

                            @if ($category->children->count() > 0)
                                <div class="text-xs text-gray-500">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100">
                                        {{ $category->children->count() }} {{ __('listings.subcategories') }}
                                    </span>
                                </div>
                            @endif

                            <!-- Selected indicator -->
                            <div class="absolute top-2 right-2 hidden selected-indicator">
                                <div class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center">
                                    <i class="ri-check-line text-white text-xs"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

            <!-- Navigation Buttons -->
            <div class="mt-6 mb-6 flex justify-between">
                <a href="{{ route('user.dashboard') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="ri-arrow-left-line mr-2"></i>
                    {{ __('listings.back_to_dashboard') }}
                </a>

                <button type="submit" id="nextButton" disabled
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ __('listings.next_step') }}
                    <i class="ri-arrow-right-line ml-2"></i>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            let selectedCategoryId = null;
            let categories = @json($categories);
            let categoryBreadcrumb = []; // Track current path
            let allCategories = {}; // Store all categories for easier lookup

            // Build category lookup object for all categories
            function buildCategoryLookup(categoryList) {
                categoryList.forEach(cat => {
                    allCategories[cat.id] = cat;
                    // Handle both 'children' and 'children_recursive' relationships
                    const children = cat.children_recursive || cat.children;
                    if (children && children.length > 0) {
                        // Update category object to use consistent 'children' property
                        cat.children = children;
                        buildCategoryLookup(children);
                    }
                });
            }

            // Initialize category lookup
            buildCategoryLookup(categories);

            function escapeHtml(value) {
                const element = document.createElement('div');
                element.textContent = value ?? '';
                return element.innerHTML;
            }

            function selectCategory(categoryId, categoryName, isMainCategory = true) {
                const category = allCategories[categoryId];

                document.querySelectorAll('.subcategory-card').forEach(card => {
                    card.classList.remove('border-blue-300');
                    card.classList.add('border-gray-200');
                });

                const clickedCard = document.querySelector(`.subcategory-card[data-category-id="${categoryId}"]`);
                clickedCard?.classList.remove('border-gray-200');
                clickedCard?.classList.add('border-blue-300');
                const currentSubcategorySection = clickedCard?.closest('.subcategorySection');

                if (currentSubcategorySection) {
                    let sibling = currentSubcategorySection.nextElementSibling;
                    while (sibling) {
                        const next = sibling.nextElementSibling;
                        if (sibling.classList.contains('subcategorySection')) sibling.remove();
                        sibling = next;
                    }
                }

                if (category.children && category.children.length > 0) {
                    document.getElementById('renderedSubcategorySection').insertAdjacentHTML('beforeend', getSubcategorySection(categoryId));
                    showSubcategories(categoryId, category.children);
                    document.getElementById('nextButton').disabled = true;
                    document.getElementById('selectedCategoryId').value = '';
                    selectedCategoryId = null;
                } else {
                    document.getElementById('nextButton').disabled = false;
                    document.getElementById('selectedCategoryId').value = categoryId;
                    selectedCategoryId = categoryId;
                }

                const sections = document.querySelectorAll('.subcategorySection');
                sections[sections.length - 1]?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            function getSubcategorySection(categoryId) {
                const subcategorySection = `<div class="subcategorySection mt-8" data-category-id="${categoryId}">
                <div class="border-t pt-6">
                    <nav id="categoryBreadcrumb" class="mb-4" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        </ol>
                    </nav>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('listings.subcategory_selection') }}</h3>
                    <div class="subcategoryGrid grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" data-category-id="${categoryId}">
                    </div>
                </div>
            </div>
`;
                return subcategorySection;
            }

            function showSubcategories(categoryId, subcategories) {
                if (subcategories.length === 0) {
                    return;
                }
                const sections = document.querySelectorAll('.subcategorySection');
                const lastSubCategorySection = sections[sections.length - 1];
                const subcategoryGrid = lastSubCategorySection?.querySelector('.subcategoryGrid');
                if (!subcategoryGrid) return;

                // Clear previous subcategories
                subcategoryGrid.replaceChildren();

                // Add subcategories
                subcategories.forEach(subcategory => {
                    const subcategoryCard = document.createElement('div');
                    subcategoryCard.className =
                        'subcategory-card relative border-2 border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors cursor-pointer';
                    subcategoryCard.setAttribute('data-category-id', subcategory.id);
                    const subcategoryName = subcategory.description?.name ?? `Kategori #${subcategory.id}`;
                    const shortDescription = subcategory.description?.short_description ?? '';
                    subcategoryCard.onclick = () => selectCategory(subcategory.id, subcategoryName, false);

                    // Show if this subcategory has children
                    const hasChildren = subcategory.children && subcategory.children.length > 0;

                    subcategoryCard.innerHTML = `
            <div class="flex items-center mb-2">
                ${subcategory.icon ? `<i class="${subcategory.icon} text-xl text-blue-600 mr-3"></i>` : `<div class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center mr-3"><i class="ri-price-tag-3-line text-blue-600 text-sm"></i></div>`}
                <h5 class="text-md font-medium text-gray-900">${escapeHtml(subcategoryName)}</h5>
            </div>
            ${shortDescription ? `<p class="text-sm text-gray-600 mb-2">${escapeHtml(shortDescription)}</p>` : ''}
            ${hasChildren ? `<div class="text-xs text-gray-500"><span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100">${subcategory.children.length} {{ __('listings.subcategories') }}</span></div>` : ''}
            <div class="absolute top-2 right-2 hidden selected-indicator">
                <div class="w-5 h-5 bg-blue-600 rounded-full flex items-center justify-center">
                    <i class="ri-check-line text-white text-xs"></i>
                </div>
            </div>
        `;

                    subcategoryGrid.appendChild(subcategoryCard);
                });

            }



            // Form validation
            document.getElementById('categoryForm').addEventListener('submit', function(e) {
                if (!selectedCategoryId) {
                    e.preventDefault();
                    alert('{{ __("listings.select_category_alert") }}');
                }
            });
        </script>
    @endpush
@endsection
