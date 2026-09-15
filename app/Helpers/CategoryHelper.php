<?php

namespace App\Helpers;

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Support\Str;
class CategoryHelper
{
    /**
     * Get all nested children category IDs for a given category
     *
     * @param \App\Models\Category $category The parent category
     * @return array Array of child category IDs including nested children
     */
    public static function getNestedChildrenCategories($category)
    {
        $ids = [];

        // Get direct children
        $children = $category->children;

        foreach ($children as $child) {
            // Add current child ID
            $ids[] = $child->id;

            // Recursively get IDs of this child's children
            if ($child->children->count() > 0) {
                $ids = array_merge($ids, self::getNestedChildrenCategories($child));
            }
        }

        return $ids;
    }

    /**
     * Update the count of the category with the count of the listings in the category and its nested children categories
     */
    public static function updateCategoryCount($category_id)
    {
        $category = Category::find($category_id);

        $category_ids = self::getNestedChildrenCategories($category);
        $category_ids[] = $category_id;

        $listings = Listing::whereIn('category_id', $category_ids)
            ->where('is_active', true)
            ->where('is_approved', true)
            ->count();

        $category->listings_count = $listings;
        $category->save();
    }

     /**
     * Full category slug path it should be generated from nested parent category names
     */
    public static function getFullCategorySlugPath(Category $category)
    {
        $slug_list = collect();
        $slug_list->prepend($category);
        $current = $category;
        while ($current->parent) {
            $slug_list->prepend($current->parent);
            $current = $current->parent;
        }

        return $slug_list->map(function ($category) {
            $description = $category->getCurrentDescription();
            $name = $description ? $description->name : 'category-' . $category->id;
            return Str::slug($name);
        })->implode('-');
    }

    /**
     * Get full title path from nested parent category names
     */
    public static function getFullTitlePath(Category $category, $reverse = true, $separator = ' / ')
    {
        $title_list = collect();
        $title_list->prepend($category);
        $current = $category;
        while ($current->parent) {
            $title_list->prepend($current->parent);
            $current = $current->parent;
        }

        $title_list = $title_list->map(function ($category) {
            $description = $category->getCurrentDescription();
            return $description ? $description->name : 'Category #' . $category->id;
        });

        if ($reverse) {
            $title_list = $title_list->reverse();
        }

        return $title_list->implode($separator);
    }
}