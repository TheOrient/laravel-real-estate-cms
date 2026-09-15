<?php

return [
    'languages' => 'Languages',
    'language_management' => 'Language Management',
    'all_languages' => 'All Languages',
    'add_language' => 'Add Language',
    'edit_language' => 'Edit Language',
    'title' => 'Title',
    'icon' => 'Icon',
    'choose_icon' => 'Choose an icon',
    'icon_hint' => 'Icon file is saved in public/uploads/icon folder with language ID and is updated with the same name on each update.',
    'current_icon' => 'Current Icon',
    'sort_order' => 'Order',
    'is_active' => 'Active',
    'is_default' => 'Default Language',
    'no_languages' => 'No languages added yet.',
    'created' => 'Language created successfully.',
    'updated' => 'Language updated successfully.',
    'deleted' => 'Language deleted successfully.',

    // Seeded-only mode
    'seeded_notice' => 'Languages are defined via config/app.php and LanguageSeeder. To add a new language, create a lang/<code>/ folder, extend the seeder and re-run migrate + seed.',
    'edit_disabled_seeded' => 'Languages are managed via the seeder; direct edits are disabled.',
];
