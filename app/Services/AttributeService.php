<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Language;
use App\Repositories\AttributeRepository;
use Illuminate\Support\Collection;

class AttributeService implements ServiceInterface
{
    protected $attributeRepository;

    public function __construct(AttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function getAllAttributes(): Collection
    {
        return $this->attributeRepository->getAllWithValues();
    }

    public function createAttribute(array $data, ?array $options = null, ?array $descriptions = null, ?array $valueDescriptions = null): Attribute
    {
        return $this->attributeRepository->createAttributeWithValues($data, $options, $descriptions, $valueDescriptions);
    }

    public function updateAttribute(Attribute $attribute, array $data, ?array $options = null, ?array $descriptions = null, ?array $valueDescriptions = null): Attribute
    {
        return $this->attributeRepository->updateAttributeWithValues($attribute, $data, $options, $descriptions, $valueDescriptions);
    }

    /**
     * Get active languages ordered by sort_order
     */
    public function getActiveLanguages(): Collection
    {
        return Language::defaultListForForms();
    }

    /**
     * Prepare empty attribute descriptions array for all active languages
     */
    public function prepareEmptyAttributeDescriptions(): array
    {
        $languages = $this->getActiveLanguages();
        $descriptions = [];

        foreach ($languages as $language) {
            $descriptions[$language->id] = [
                'name' => '',
                'input_placeholder' => '',
            ];
        }

        return $descriptions;
    }

    /**
     * Prepare attribute descriptions for all active languages
     */
    public function prepareAttributeDescriptions(Attribute $attribute): array
    {
        $languages = $this->getActiveLanguages();
        $attribute->load('descriptions');
        $descriptions = [];

        foreach ($languages as $language) {
            $description = $attribute->descriptions->where('language_id', $language->id)->first();
            $descriptions[$language->id] = [
                'name' => $description ? $description->name : '',
                'input_placeholder' => $description ? $description->input_placeholder : '',
            ];
        }

        return $descriptions;
    }

    /**
     * Prepare empty attribute value descriptions array for all active languages
     */
    public function prepareEmptyAttributeValueDescriptions(int $count = 1): array
    {
        $languages = $this->getActiveLanguages();
        $valueDescriptions = [];

        for ($i = 0; $i < $count; $i++) {
            $valueDescriptions[$i] = [];
            foreach ($languages as $language) {
                $valueDescriptions[$i][$language->id] = [
                    'value' => '',
                ];
            }
        }

        return $valueDescriptions;
    }

    /**
     * Prepare attribute value descriptions for all active languages
     */
    public function prepareAttributeValueDescriptions(Attribute $attribute): array
    {
        $languages = $this->getActiveLanguages();
        $attribute->load('values.descriptions');
        $valueDescriptions = [];

        foreach ($attribute->values as $index => $value) {
            $valueDescriptions[$index] = [];
            foreach ($languages as $language) {
                $description = $value->descriptions->where('language_id', $language->id)->first();
                $valueDescriptions[$index][$language->id] = [
                    'value' => $description ? $description->value : '',
                ];
            }
        }

        return $valueDescriptions;
    }

    public function deleteAttribute(Attribute $attribute): bool
    {
        return $this->attributeRepository->delete($attribute->id);
    }

    /**
     * Get data for showing category attribute assignment page.
     */
    public function getCategoryAttributeData(): array
    {
        return [
            'categories' => $this->attributeRepository->getParentCategoriesWithChildren(),
            'attributes' => $this->attributeRepository->getAllAttributes()
        ];
    }

    /**
     * Assign attributes to a category with position data.
     */
    public function assignAttributesToCategory(Category $category, array $attributeData): bool
    {
        return $this->attributeRepository->syncCategoryAttributes($category, $attributeData);
    }

    /**
     * Get all attributes for a category (including from parent categories).
     */
    public function getCategoryAttributes(Category $category): Collection
    {
        return $category->getAllAttributes();
    }

    /**
     * Get filterable attributes for a category.
     */
    public function getFilterableAttributes(Category $category): Collection
    {
        return $category->getFilterableAttributes();
    }
}
