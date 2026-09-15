<?php

return [
    // Category titles
    'categories' => 'Categories',
    'category' => 'Category',
    'all_categories' => 'All Categories',
    'add_category' => 'Add New Category',
    'edit_category' => 'Edit Category',
    'category_details' => 'Category Information',

    // Fields
    'name' => 'Category Name',
    'parent_category' => 'Parent Category',
    'no_parent' => '-- No parent category --',
    'icon' => 'Icon (Remix Icon Class)',
    'icon_info' => 'Enter an icon class from the Remix Icon library.',
    'icon_placeholder' => 'E.g: ri-home-line',
    'short_description' => 'Short Description',
    'description' => 'Description',
    'slug' => 'Slug',
    'slug_info' => 'If left empty, it will be automatically generated from the category name. It is saved as a full path including parent categories.',
    'slug_auto_generated' => 'Slug is automatically generated and updated from the category name.',
    'listing_count' => 'Listing Count',
    'is_filterable' => 'Filterable Category',
    'filterable_info' => 'This option determines whether filtering options will be displayed on the category page.',
    'translations' => 'Translations',

    // Messages
    'created' => 'Category created successfully.',
    'updated' => 'Category updated successfully.',
    'deleted' => 'Category deleted successfully.',
    'cannot_delete_has_listings' => 'This category cannot be deleted because it contains listings.',
    'cannot_delete_has_children' => 'This category cannot be deleted because it has subcategories.',
    'parent_cannot_be_child' => 'A category cannot be selected as its own subcategory.',
    'no_categories' => 'No categories yet.',
    'confirm_delete' => 'Are you sure you want to delete this category?',
];
