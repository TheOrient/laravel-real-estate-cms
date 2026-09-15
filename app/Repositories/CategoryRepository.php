<?php

namespace App\Repositories;

use App\Constants\ListingAttributeDisplayTypeConstant;
use App\Helpers\CategoryHelper;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Language;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CategoryRepository extends BaseRepository
{
    public function model(): string
    {
        return Category::class;
    }

    public function getAllWithParent(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->with('parent')->orderBy('id', 'desc')->paginate($perPage);
    }

    public function getAllExcept(int $categoryId, array $childrenIds): Collection
    {
        return $this->model
            ->where('id', '!=', $categoryId)
            ->whereNotIn('id', $childrenIds)
            ->get();
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function hasListings(Category $category): bool
    {
        return $category->listings()->count() > 0;
    }

    public function hasChildren(Category $category): bool
    {
        return $category->children()->count() > 0;
    }

    public function getChildrenIds(Category $category): array
    {
        $ids = [];

        foreach ($category->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getChildrenIds($child));
        }

        return $ids;
    }

    public function updateCategorySlug(Category $category): void
    {
        $category->slug = $this->getFullCategorySlugPath($category);
        $category->save();
    }

    public function updateChildrenSlugs(Category $category): void
    {
        foreach ($category->children as $child) {
            $child->slug = $this->getFullCategorySlugPath($child);
            $child->save();

            $this->updateChildrenSlugs($child);
        }
    }

    private function getFullCategorySlugPath(Category $category): string
    {
        return app(\App\Helpers\CategoryHelper::class)->getFullCategorySlugPath($category);
    }

    public function getWithParents()
    {
        return $this->model->with('parent')->get();
    }

    public function getActiveWithChildren()
    {
        $defaultLanguageId = Language::defaultCached()?->id;

        // Get categories with descriptions
        $categories = $this->model->where('is_active', true)
            ->whereNull('parent_id')
            ->leftJoin('category_descriptions', function($join) use ($defaultLanguageId) {
                $join->on('categories.id', '=', 'category_descriptions.category_id')
                     ->where('category_descriptions.language_id', '=', $defaultLanguageId);
            })
            ->select('categories.*', 'category_descriptions.name as description_name')
            ->orderBy('category_descriptions.name')
            ->get();

        // Load children with descriptions and sort them
        $categories->load(['descriptions', 'children' => function ($query) {
            $query->where('is_active', true);
        }, 'children.descriptions' => function ($query) use ($defaultLanguageId) {
            $query->where('language_id', $defaultLanguageId);
        }]);

        // Sort children by name using collection
        foreach ($categories as $category) {
            $category->setRelation('children', $category->children->sortBy(function($child) use ($defaultLanguageId) {
                $desc = $child->descriptions->where('language_id', $defaultLanguageId)->first();
                return $desc ? $desc->name : 'zzz';
            })->values());
        }

        return $categories;
    }

    public function getActiveCategoryBySlug(string $slug)
    {
        $defaultLanguageId = Language::defaultCached()?->id;

        $category = $this->model->where('category_descriptions.slug', $slug)
            ->where('categories.is_active', true)
            ->join('category_descriptions', function($join) use ($defaultLanguageId) {
                $join->on('categories.id', '=', 'category_descriptions.category_id')
                     ->where('category_descriptions.language_id', '=', $defaultLanguageId);
            })
            ->select('categories.*')
            ->with(['children' => function ($query) {
                $query->where('is_active', true);
            }, 'children.descriptions' => function ($query) use ($defaultLanguageId) {
                $query->where('language_id', $defaultLanguageId);
            }])
            ->firstOrFail();

        // Sort children by name using collection
        $category->setRelation('children', $category->children->sortBy(function($child) use ($defaultLanguageId) {
            $desc = $child->descriptions->where('language_id', $defaultLanguageId)->first();
            return $desc ? $desc->name : 'zzz';
        })->values());

        return $category;
    }

    public function getCategoryWithChildren(Category $category)
    {
        // Get all children category IDs including nested ones using our consistent method
        $childrenIds = $this->getChildrenIds($category);

        // Include the current category ID as well
        return array_merge([$category->id], $childrenIds);
    }

    public function getFilterableAttributes(Category $category)
    {
        return $category->getFilterableAttributes();
    }

    public function getAttributeValues(Attribute $attribute)
    {
        return AttributeValue::where('attribute_id', $attribute->id)
            ->orderBy('order')
            ->get();
    }

    public function getParentCategoriesForBreadcrumb(Category $category): Collection
    {
        $breadcrumb = collect();
        $current = $category;

        while ($current->parent) {
            $breadcrumb->prepend($current->parent);
            $current = $current->parent;
        }

        return $breadcrumb;
    }

    public function findBySlug(string $slug): ?Category
    {
        $defaultLanguageId = Language::defaultCached()?->id;

        // First try to find without is_active filter to see if the category exists at all
        $category = $this->model->join('category_descriptions', function($join) use ($defaultLanguageId) {
                $join->on('categories.id', '=', 'category_descriptions.category_id')
                     ->where('category_descriptions.language_id', '=', $defaultLanguageId);
            })
            ->where('category_descriptions.slug', $slug)
            ->select('categories.*')
            ->first();

        if (!$category) {
            \Log::debug("Category with slug {$slug} not found at all");
            return null;
        }

        $description = $category->descriptions->where('language_id', $defaultLanguageId)->first();
        $categoryName = $description ? $description->name : 'Category #' . $category->id;
        \Log::debug("Found category with slug {$slug}: ID={$category->id}, Name={$categoryName}, is_active={$category->is_active}");

        // Now get with all the necessary relationships
        $category = $this->model->join('category_descriptions', function($join) use ($defaultLanguageId) {
                $join->on('categories.id', '=', 'category_descriptions.category_id')
                     ->where('category_descriptions.language_id', '=', $defaultLanguageId);
            })
            ->where('category_descriptions.slug', $slug)
            ->select('categories.*')
            ->with(['children' => function ($query) {
                $query->where('is_active', true);
            }, 'children.descriptions' => function ($query) use ($defaultLanguageId) {
                $query->where('language_id', $defaultLanguageId);
            }])
            ->firstOrFail();

        // Sort children by name using collection
        $category->setRelation('children', $category->children->sortBy(function($child) use ($defaultLanguageId) {
            $desc = $child->descriptions->where('language_id', $defaultLanguageId)->first();
            return $desc ? $desc->name : 'zzz';
        })->values());

        return $category;
    }

    public function getCategoryListings(?Category $category, array $filters = [], int $perPage = 15)
    {
        $categoryName = $category ? ($category->getCurrentDescription() ? $category->getCurrentDescription()->name : 'Category #' . $category->id) : 'All';
        \Log::debug("Fetching listings for category: " . ($category ? "{$categoryName} (ID: {$category->id})" : "All"));

        $query = app(\App\Models\Listing::class)->with([
                'descriptions',
                'images',
                'category.descriptions',
                'city',
                'district',
                'neighborhood',
            ])
            ->where('is_active', true)
            ->where('is_approved', true);

        // Resolve category from filters when no explicit Category was passed.
        // Lets the home-page search form pick a parent slug ("daire",
        // "villa") without the URL needing to be /category/{slug}.
        if (! $category && ! empty($filters['category_slug'])) {
            $category = $this->model->whereHas('descriptions', function ($q) use ($filters) {
                $q->where('slug', $filters['category_slug']);
            })->first();
        }

        // If a category is provided, filter by that category and its children
        if ($category) {
            $categoryIds = $this->getCategoryWithChildrenIds($category);
            \Log::debug("Category and child IDs: " . implode(", ", $categoryIds));
            $query->whereIn('category_id', $categoryIds);
        }

        // Sale / rent filter: scope to categories whose name starts with
        // the requested word. Composes with the category filter above.
        if (! empty($filters['type'])) {
            $prefix = $filters['type'] === 'satilik' ? 'Satılık' : ($filters['type'] === 'kiralik' ? 'Kiralık' : null);
            if ($prefix) {
                $defaultLanguageId = Language::defaultCached()?->id;
                $query->whereIn('category_id', function ($sub) use ($prefix, $defaultLanguageId) {
                    $sub->select('category_id')
                        ->from('category_descriptions')
                        ->where('name', 'like', $prefix . '%')
                        ->when($defaultLanguageId, fn ($q) => $q->where('language_id', $defaultLanguageId));
                });
            }
        }

        // Apply location filters
        if (!empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (!empty($filters['district_id'])) {
            $query->where('district_id', $filters['district_id']);
        }

        if (!empty($filters['neighborhood_id'])) {
            $query->where('neighborhood_id', $filters['neighborhood_id']);
        }

        // Apply price filters
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Apply search filter
        if (!empty($filters['s'])) {
            $searchTerm = $filters['s'];
            $query->whereHas('descriptions', function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Apply attribute filters
        foreach ($filters as $key => $value) {
            if (strpos($key, 'attribute_') === 0 && !empty($value)) {
                $attributeId = substr($key, 10); // Remove 'attribute_' prefix to get the ID

                // Get the attribute to check its type
                $attribute = app(Attribute::class)->find($attributeId);
                if (!$attribute) continue;

                if (in_array($attribute->display_type_search, ListingAttributeDisplayTypeConstant::getSingularPredefinedValueTypes())) {
                    // For select and radio, we search for the specific value ID
                    $query->whereHas('attributeValues', function ($q) use ($value) {
                        $q->where('attribute_values.id', $value);
                    });
                } elseif (in_array($attribute->display_type_search,ListingAttributeDisplayTypeConstant::getMultiplePredefinedValueTypes())) {
                    // For checkboxes (multi-select)
                    if (is_array($value)) {
                        $query->whereHas('attributeValues', function ($q) use ($value) {
                            $q->whereIn('attribute_values.id', $value);
                        });
                    } else {
                        $query->whereHas('attributeValues', function ($q) use ($value) {
                            $q->where('attribute_values.id', $value);
                        });
                    }
                } else if (in_array($attribute->display_type_search, ListingAttributeDisplayTypeConstant::getFreeTextInputTypes())) {
                    // For free input fields (text, number, etc.)
                    $query->whereHas('customAttributeValues', function ($q) use ($attributeId, $value) {
                        $q->where('attribute_id', $attributeId)
                            ->where('value', 'like', "%{$value}%");
                    });
                }else{
                    \Log::debug("Unknown attribute display type for attribute ID {$attributeId}: {$attribute->display_type_search}");
                }
            }
        }

        // Apply sorting
        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    public function getCategoryWithChildrenIds(Category $category): array
    {
        $ids = [$category->id];
        $ids = array_merge($ids, $this->getChildrenIds($category));
        return $ids;
    }
}
