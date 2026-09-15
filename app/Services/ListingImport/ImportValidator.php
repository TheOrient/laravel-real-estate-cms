<?php

namespace App\Services\ListingImport;

use App\Constants\ListingAttributeDisplayTypeConstant as DisplayType;
use App\Constants\UserRolesConstant;
use App\Models\Category;
use App\Models\Language;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;
use JsonException;

class ImportValidator
{
    /** Validate and encode every photo before writing anything to the database or disk. */
    public function prepare(string $manifest, mixed $agentId, bool $publish): array
    {
        $file = realpath($manifest);
        if (! $file || ! is_file($file) || ! is_readable($file)
            || filesize($file) > config('listing_import.max_manifest_bytes')) {
            $this->fail('file', 'invalid_manifest');
        }

        try {
            $data = json_decode(file_get_contents($file), true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->fail('file', 'invalid_json');
        }

        $fields = ['reference', 'title', 'description', 'price', 'category_id', 'city_id',
            'district_id', 'neighborhood_id', 'latitude', 'longitude', 'attributes',
            'photos', 'video_url', 'allow_whatsapp'];
        if (! is_array($data) || array_is_list($data)) {
            $this->fail('file', 'invalid_json');
        }
        if (array_diff(array_keys($data), $fields)) {
            $this->fail('file', 'unknown_fields');
        }
        foreach (['title', 'description'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }

        Validator::make(['agent' => $agentId], ['agent' => ['required', 'integer', 'min:1']])->validate();
        if (! User::whereKey($agentId)->where('is_active', true)
            ->whereIn('user_role', [UserRolesConstant::ADMIN, UserRolesConstant::AGENT])->exists()) {
            $this->fail('agent', 'invalid_agent');
        }

        $data = Validator::make($data, [
            'reference' => ['required', 'string', 'max:80', 'regex:/\A[a-z0-9][a-z0-9_-]*\z/'],
            'title' => ['required', 'string', 'min:5', 'max:200'],
            'description' => ['required', 'string', 'min:20', 'max:20000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')->where('is_active', true)],
            'district_id' => ['required', 'integer', Rule::exists('districts', 'id')
                ->where('is_active', true)->where('city_id', $data['city_id'] ?? null)],
            'neighborhood_id' => ['nullable', 'integer', Rule::exists('neighborhoods', 'id')
                ->where('is_active', true)->where('district_id', $data['district_id'] ?? null)],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'attributes' => ['sometimes', 'array', 'max:100'],
            'photos' => [$publish ? 'required' : 'sometimes', 'array', 'list', 'min:'.($publish ? 1 : 0),
                'max:'.config('listing_import.max_photos')],
            'photos.*' => ['required', 'string', 'distinct', 'max:255'],
            'video_url' => ['bail', 'nullable', 'string', 'url:https', 'max:255', function ($key, $value, $fail) {
                if (! in_array(strtolower(parse_url($value, PHP_URL_HOST) ?? ''), config('listing_import.video_hosts'), true)) {
                    $fail(__('listing_import.invalid_video'));
                }
            }],
            'allow_whatsapp' => ['sometimes', 'boolean'],
        ])->validate();

        foreach (['title', 'description'] as $field) {
            $data[$field] = trim($data[$field]);
            if ($data[$field] !== strip_tags($data[$field])) {
                $this->fail($field, 'plain_text');
            }
        }
        if (Str::slug($data['title']) === '') {
            $this->fail('title', 'invalid_title');
        }

        $language = Language::where('code', config('listing_import.source_locale'))->where('is_active', true)->first();
        if (! $language) {
            $this->fail('language', 'missing_language');
        }

        $category = Category::findOrFail($data['category_id']);
        $visited = [];
        for ($parent = $category; $parent; $parent = $parent->parent) {
            if (! $parent->is_active || isset($visited[$parent->id])) {
                $this->fail('category_id', 'invalid_category');
            }
            $visited[$parent->id] = true;
        }
        if ($category->children()->exists()) {
            $this->fail('category_id', 'leaf_category');
        }

        $attributes = $this->attributes($category, $data['attributes'] ?? [], $publish);
        $photos = [];
        foreach ($data['photos'] ?? [] as $index => $path) {
            $photos[] = $this->photo(dirname($file), $path, $index);
        }

        $values = [
            'price' => number_format((float) $data['price'], 2, '.', ''),
            'category_id' => (int) $data['category_id'], 'user_id' => (int) $agentId,
            'city_id' => (int) $data['city_id'], 'district_id' => (int) $data['district_id'],
            'neighborhood_id' => isset($data['neighborhood_id']) ? (int) $data['neighborhood_id'] : null,
            'latitude' => isset($data['latitude']) ? round((float) $data['latitude'], 8) : null,
            'longitude' => isset($data['longitude']) ? round((float) $data['longitude'], 8) : null,
            'video_url' => $data['video_url'] ?? null,
            'allow_whatsapp' => (bool) ($data['allow_whatsapp'] ?? false),
        ];
        $description = ['language_id' => $language->id, 'title' => $data['title'], 'description' => $data['description']];
        // Publication mode is excluded: the exact same draft can subsequently be published.
        $fingerprint = hash('sha256', json_encode([$values, $description, $attributes,
            array_column($photos, 'hash')], JSON_THROW_ON_ERROR));

        return compact('values', 'description', 'attributes', 'photos', 'fingerprint')
            + ['reference' => $data['reference']];
    }

    private function attributes(Category $category, array $input, bool $publish): array
    {
        $available = $category->getAllAttributes(['values'])->keyBy('id');
        $result = ['options' => [], 'custom' => []];
        foreach (array_keys($input) as $id) {
            if (! $available->has($id)) {
                $this->fail("attributes.$id", 'unknown_attribute');
            }
        }
        foreach ($available as $id => $attribute) {
            $value = $input[$id] ?? null;
            if ($value === null || $value === '' || $value === []) {
                if ($publish && $attribute->is_required) {
                    $this->fail("attributes.$id", 'required_attribute');
                }

                continue;
            }
            if (in_array($attribute->display_type_user_panel, DisplayType::getFreeTextInputTypes(), true)) {
                $rules = ['required', 'string', 'max:2000'];
                if ($attribute->display_type_user_panel === DisplayType::NUMBER) {
                    $rules = ['required', 'numeric'];
                    if ($attribute->min_value !== null) {
                        $rules[] = 'min:'.$attribute->min_value;
                    }
                    if ($attribute->max_value !== null) {
                        $rules[] = 'max:'.$attribute->max_value;
                    }
                }
                Validator::make(['attributes' => [$id => $value]], ["attributes.$id" => $rules])->validate();
                if ((string) $value !== strip_tags((string) $value)) {
                    $this->fail("attributes.$id", 'plain_text');
                }
                $result['custom'][$id] = (string) $value;
            } else {
                $multiple = in_array($attribute->display_type_user_panel, DisplayType::getMultiplePredefinedValueTypes(), true);
                $options = is_array($value) ? $value : [$value];
                Validator::make(['options' => $options], [
                    'options' => ['required', 'array', 'list', 'max:'.($multiple ? 100 : 1)],
                    'options.*' => ['integer', 'distinct', Rule::in($attribute->values->pluck('id')->all())],
                ])->validate();
                array_push($result['options'], ...array_map('intval', $options));
            }
        }
        sort($result['options']);
        ksort($result['custom']);

        return $result;
    }

    private function photo(string $root, string $relative, int|string $index): array
    {
        $key = "photos.$index";
        // Only regular files inside this bundle; no remote fetches or escaped symlinks.
        $path = realpath($root.DIRECTORY_SEPARATOR.$relative);
        if (str_starts_with($relative, '/') || str_contains($relative, '\\')
            || in_array('..', explode('/', $relative), true) || ! $path
            || ! str_starts_with($path, $root.DIRECTORY_SEPARATOR)
            || ! is_file($path) || ! is_readable($path)
            || filesize($path) > config('listing_import.max_photo_bytes')) {
            $this->fail($key, 'invalid_photo');
        }
        $size = @getimagesize($path);
        if (! $size || ! in_array($size['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)
            || config('listing_import.max_photo_pixels') < $size[0] * $size[1]) {
            $this->fail($key, 'invalid_photo');
        }

        try {
            $bytes = file_get_contents($path);
            $image = ImageManager::gd(decodeAnimation: false, strip: true)->read($bytes);
            $encoded = (string) $image->scaleDown(
                width: config('listing_import.image_max_width'), height: config('listing_import.image_max_height')
            )->toWebp(quality: config('listing_import.image_quality'));
        } catch (\Throwable) {
            $this->fail($key, 'invalid_photo');
        }

        return ['hash' => hash('sha256', $bytes), 'encoded' => $encoded];
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => __('listing_import.'.$message)]);
    }
}
