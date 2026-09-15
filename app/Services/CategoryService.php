<?php

namespace App\Services;

use App\Constants\ListingAttributeDisplayTypeConstant;
use App\Helpers\CategoryHelper;
use App\Models\Category;
use App\Models\CategoryDescription;
use App\Models\Language;
use App\Repositories\CategoryRepository;
use App\Repositories\ListingRepository;
use App\Repositories\LocationRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Services\AttributeService;

class CategoryService implements ServiceInterface
{
    protected $categoryRepository;
    protected $listingRepository;
    protected $locationRepository;
    protected $attributeService;

    public function __construct(
        CategoryRepository $categoryRepository,
        ListingRepository $listingRepository,
        LocationRepository $locationRepository,
        AttributeService $attributeService
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->listingRepository = $listingRepository;
        $this->locationRepository = $locationRepository;
        $this->attributeService = $attributeService;
    }

    public function getCategoryForListing($categoryId)
    {
        return $this->categoryRepository->find($categoryId);
    }

    public function getAllCategoriesWithParents()
    {
        return $this->categoryRepository->getWithParents();
    }

    public function getActiveCategoriesWithChildren()
    {
        return $this->categoryRepository->getActiveWithChildren();
    }

    public function getAllCategories(int $perPage = 20): LengthAwarePaginator
    {
        return $this->categoryRepository->getAllWithParent($perPage);
    }

    public function getAllCategoriesForSelection(): Collection
    {
        return $this->categoryRepository->getAll();
    }

    public function getSelectableParents(Category $category): Collection
    {
        $childrenIds = $this->categoryRepository->getChildrenIds($category);
        return $this->categoryRepository->getAllExcept($category->id, $childrenIds);
    }

    /**
     * Get active languages ordered by sort_order
     */
    public function getActiveLanguages(): Collection
    {
        return Language::defaultListForForms();
    }

    /**
     * Prepare empty descriptions array for all active languages
     */
    public function prepareEmptyDescriptions(): array
    {
        $languages = $this->getActiveLanguages();
        $descriptions = [];

        foreach ($languages as $language) {
            $descriptions[$language->id] = [
                'name' => '',
                'short_description' => '',
                'description' => '',
            ];
        }

        return $descriptions;
    }

    /**
     * Prepare category descriptions for all active languages
     */
    public function prepareCategoryDescriptions(Category $category): array
    {
        $languages = $this->getActiveLanguages();
        $category->load('descriptions');
        $descriptions = [];

        foreach ($languages as $language) {
            $description = $category->descriptions->where('language_id', $language->id)->first();
            $descriptions[$language->id] = [
                'name' => $description ? $description->name : '',
                'short_description' => $description ? $description->short_description : '',
                'description' => $description ? $description->description : '',
            ];
        }

        return $descriptions;
    }

    public function createCategory(array $data): Category
    {
        // Extract descriptions from data
        $descriptions = $data['descriptions'] ?? [];
        unset($data['descriptions']);

        // Create the category (without translatable fields)
        $category = $this->categoryRepository->create($data);

        // Create descriptions for each language
        foreach ($descriptions as $langId => $descriptionData) {
            // Always auto-generate slug from name
            $descriptionData['slug'] = Str::slug($descriptionData['name']);

            CategoryDescription::create([
                'category_id' => $category->id,
                'language_id' => $descriptionData['language_id'],
                'name' => $descriptionData['name'],
                'slug' => $descriptionData['slug'],
                'short_description' => $descriptionData['short_description'] ?? null,
                'description' => $descriptionData['description'] ?? null,
            ]);
        }

        // Update slug with full path (includes parent path) for all languages
        $this->updateCategorySlugs($category);

        return $category;
    }

    public function updateCategory(Category $category, array $data): Category
    {
        // Extract descriptions from data
        $descriptions = $data['descriptions'] ?? [];
        unset($data['descriptions']);

        // Update the category (without translatable fields)
        $category = $this->categoryRepository->update($data, $category->id);

        // Update or create descriptions for each language
        foreach ($descriptions as $langId => $descriptionData) {
            // Always auto-generate slug from name
            $descriptionData['slug'] = Str::slug($descriptionData['name']);

            CategoryDescription::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'language_id' => $descriptionData['language_id'],
                ],
                [
                    'name' => $descriptionData['name'],
                    'slug' => $descriptionData['slug'],
                    'short_description' => $descriptionData['short_description'] ?? null,
                    'description' => $descriptionData['description'] ?? null,
                ]
            );
        }

        // Update slug with full path (includes parent path) for all languages
        $this->updateCategorySlugs($category);

        // Update children slugs (if parent changed)
        $this->updateChildrenSlugs($category);

        return $category;
    }

    /**
     * Update category slugs for all languages with full path (includes parent path)
     */
    protected function updateCategorySlugs(Category $category): void
    {
        $descriptions = $category->descriptions;

        foreach ($descriptions as $description) {
            $fullSlug = $this->getFullCategorySlugPath($category, $description->language_id);
            $description->slug = $fullSlug;
            $description->save();
        }
    }

    /**
     * Update children slugs for all languages
     */
    protected function updateChildrenSlugs(Category $category): void
    {
        foreach ($category->children as $child) {
            $this->updateCategorySlugs($child);
            $this->updateChildrenSlugs($child);
        }
    }

    /**
     * Get full category slug path including parent path for a specific language
     */
    protected function getFullCategorySlugPath(Category $category, int $languageId): string
    {
        $slugParts = [];
        $current = $category;

        // Build slug path from current to root
        while ($current) {
            $description = $current->descriptions->where('language_id', $languageId)->first();
            if ($description && $description->name) {
                // Always generate slug from name
                $slugParts[] = Str::slug($description->name);
            } else {
                // Fallback: use category ID if no description found
                $slugParts[] = 'category-' . $current->id;
            }
            $current = $current->parent;
        }

        // Reverse to get root to current order
        $slugParts = array_reverse($slugParts);

        return implode('/', $slugParts);
    }

    public function deleteCategory(Category $category): bool
    {
        // Check if category has listings
        if ($this->categoryRepository->hasListings($category)) {
            throw new \Exception(__('admin/categories.cannot_delete_has_listings'));
        }

        // Check if category has children
        if ($this->categoryRepository->hasChildren($category)) {
            throw new \Exception(__('admin/categories.cannot_delete_has_children'));
        }

        return $this->categoryRepository->delete($category->id);
    }

    /**
     * Get data for category page display with optional filtering
     */
    public function getCategoryPageData(string $slug, array $filters = []): array
    {
        // Handle "tumu" (all listings) slug
        $category = ($slug === 'tumu' || $slug === __('slug.all'))
            ? null
            : $this->categoryRepository->findBySlug($slug);

        $listings = $this->categoryRepository->getCategoryListings($category, $filters);
        $attributes = $category
            ? $this->attributeService->getFilterableAttributes($category)
            : collect();

        // Get total number of listings
        $total_listings = $listings->total();

        // Get breadcrumb data if category exists
        $breadcrumb = $category
            ? $this->categoryRepository->getParentCategoriesForBreadcrumb($category)
            : collect();

        // Get location data
        $cities = $this->locationRepository->getAllCities();
        $default_districts = !empty($filters['city_id'])
            ? $this->locationRepository->getDistrictsByCity($filters['city_id'])
            : collect();
        $default_neighborhoods = !empty($filters['district_id'])
            ? $this->locationRepository->getNeighborhoodsByDistrict($filters['district_id'])
            : collect();

        // Get attribute values for filterable attributes
        $attributeValues = [];
        foreach ($attributes as $attribute) {
            $attributeValues[$attribute->id] = $this->categoryRepository->getAttributeValues($attribute);
        }

        // Get full title path
        $full_title_path = $category
            ? app(\App\Helpers\CategoryHelper::class)->getFullTitlePath($category, true)
            : __('slug.all_listings_title');

        return [
            'category' => $category,
            'listings' => $listings,
            'attributes' => $attributes,
            'filters' => $filters,
            'full_title_path' => $full_title_path,
            'breadcrumb' => $breadcrumb,
            'cities' => $cities,
            'default_districts' => $default_districts,
            'default_neighborhoods' => $default_neighborhoods,
            'filterableAttributes' => $attributes,
            'attributeValues' => $attributeValues,
            'total_listings' => $total_listings
        ];
    }

    protected function getFilteredListings(array $categoryIds, array $filters): LengthAwarePaginator
    {
        // Start query
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $this->listingRepository->with([])
            ->whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->with([
                'category',
                'city',
                'district',
                'images',
                'attributeValues.attribute',
                'customAttributeValues.attribute',
            ])
            ->orderBy('created_at', 'desc');

        // Add price filters
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Add location filters
        if (!empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (!empty($filters['district_id'])) {
            $query->where('district_id', $filters['district_id']);
        }

        if (!empty($filters['neighborhood_id'])) {
            $query->where('neighborhood_id', $filters['neighborhood_id']);
        }

        // Add attribute filters
        if (!empty($filters['attribute_filters'])) {
            foreach ($filters['attribute_filters'] as $key => $value) {
                if (strpos($key, 'attribute_') === 0 && $value) {
                    $attributeId = substr($key, 10);

                    // Get the attribute to check its type
                    $attribute = app('App\Models\Attribute')->find($attributeId);

                    if (!$attribute) continue;

                    if (in_array($attribute->display_type_search, ListingAttributeDisplayTypeConstant::getPredefinedValueTypes())) {
                        // For predefined values
                        $query->whereHas('attributeValues', function ($query) use ($value, $attributeId) {
                            if (is_array($value)) {
                                $query->whereIn('attribute_values.id', $value)
                                    ->where('attribute_values.attribute_id', $attributeId);
                            } else {
                                $query->where('attribute_values.id', $value)
                                    ->where('attribute_values.attribute_id', $attributeId);
                            }
                        });
                    } else {
                        // For free input values
                        $query->whereHas('customAttributeValues', function ($query) use ($value, $attributeId) {
                            $query->where('attribute_id', $attributeId)
                                ->where('value', 'like', "%{$value}%");
                        });
                    }
                }
            }
        }

        // Set sort order
        if (!empty($filters['sort'])) {
            $query->orderBy('created_at', $filters['sort']);
        }

        $listings = $query->paginate(10);
        $listings = $listings->appends(request()->all());

        return $listings;
    }
}
