<?php

namespace App\Console\Commands;

use App\Constants\UserRolesConstant;
use App\Models\Category;
use App\Models\City;
use App\Models\Language;
use App\Models\User;
use Illuminate\Console\Command;
use Throwable;

class ListingImportCatalog extends Command
{
    protected $signature = 'listings:import-catalog {--category=}';

    public function __construct()
    {
        parent::__construct();
        $this->setDescription(__('listing_import.catalog_description'));
    }

    public function handle(): int
    {
        try {
            $languageId = Language::where('code', config('listing_import.source_locale'))->value('id');
            $description = fn ($model) => $model->descriptions->firstWhere('language_id', $languageId)
                ?? $model->descriptions->first();
            $categoryId = $this->option('category');
            if ($categoryId !== null && (! ctype_digit((string) $categoryId) || ! Category::whereKey($categoryId)->where('is_active', true)->exists())) {
                $this->error(__('listing_import.invalid_category'));

                return self::FAILURE;
            }

            $categories = Category::with('descriptions')->where('is_active', true)
                ->whereDoesntHave('children')->orderBy('id')->get();
            $catalog = [
                'source_locale' => config('listing_import.source_locale'),
                'agents' => User::where('is_active', true)
                    ->whereIn('user_role', [UserRolesConstant::ADMIN, UserRolesConstant::AGENT])
                    ->get(['id', 'first_name', 'last_name', 'user_role'])->toArray(),
                'categories' => $categories->map(fn ($category) => [
                    'id' => $category->id, 'name' => $description($category)?->name,
                    'slug' => $description($category)?->slug,
                ])->all(),
                'locations' => City::where('is_active', true)->with([
                    'districts' => fn ($q) => $q->where('is_active', true),
                    'districts.neighborhoods' => fn ($q) => $q->where('is_active', true),
                ])->get()->map(fn ($city) => [
                    'id' => $city->id, 'name' => $city->name,
                    'districts' => $city->districts->map(fn ($district) => [
                        'id' => $district->id, 'name' => $district->name,
                        'neighborhoods' => $district->neighborhoods->map->only(['id', 'name'])->all(),
                    ])->all(),
                ])->all(),
            ];
            if ($categoryId !== null) {
                $category = Category::findOrFail($categoryId);
                $catalog['attributes'] = $category->getAllAttributes(['descriptions', 'values.descriptions'])
                    ->map(fn ($attribute) => [
                        'id' => $attribute->id, 'name' => $description($attribute)?->name,
                        'type' => $attribute->display_type_user_panel, 'required_to_publish' => $attribute->is_required,
                        'min' => $attribute->min_value, 'max' => $attribute->max_value,
                        'options' => $attribute->values->map(fn ($value) => [
                            'id' => $value->id, 'name' => $description($value)?->value,
                        ])->all(),
                    ])->values()->all();
            }
            $this->line(json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error(__('listing_import.database_failed'));

            return self::FAILURE;
        }
    }
}
