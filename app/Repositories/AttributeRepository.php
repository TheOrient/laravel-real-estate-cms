<?php

namespace App\Repositories;

use App\Constants\ListingAttributeDisplayTypeConstant;
use App\Models\Attribute;
use App\Models\AttributeDescription;
use App\Models\AttributeValue;
use App\Models\AttributeValueDescription;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttributeRepository extends BaseRepository
{
    public function model(): string
    {
        return Attribute::class;
    }

    public function getAllWithValues(): Collection
    {
        return $this->model->with(['values.descriptions', 'descriptions'])->get();
    }

    public function createAttributeWithValues(array $attributeData, ?array $options = null, ?array $descriptions = null, ?array $valueDescriptions = null): Attribute
    {
        DB::beginTransaction();
        try {
            // Extract descriptions from attributeData
            $descriptions = $descriptions ?? [];

            // Remove translatable fields from attributeData
            unset($attributeData['name'], $attributeData['input_placeholder']);

            // Create the attribute (without translatable fields)
            $attribute = $this->create($attributeData);

            // Create descriptions for each language
            foreach ($descriptions as $langId => $descriptionData) {
                AttributeDescription::create([
                    'attribute_id' => $attribute->id,
                    'language_id' => $descriptionData['language_id'],
                    'name' => $descriptionData['name'],
                    'input_placeholder' => $descriptionData['input_placeholder'] ?? null,
                ]);
            }

            // Create attribute values if it has predefined value descriptions
            if (in_array($attributeData['display_type_user_panel'], ListingAttributeDisplayTypeConstant::getPredefinedValueTypes()) && is_array($valueDescriptions) && !empty($valueDescriptions)) {
                foreach ($valueDescriptions as $index => $valueData) {
                    $attributeValue = AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'order' => $index,
                    ]);

                    // Create value descriptions for each language
                    if (is_array($valueData)) {
                        foreach ($valueData as $langId => $langValueData) {
                            if (isset($langValueData['language_id']) && isset($langValueData['value'])) {
                                AttributeValueDescription::create([
                                    'attribute_value_id' => $attributeValue->id,
                                    'language_id' => $langValueData['language_id'],
                                    'value' => $langValueData['value'],
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            return $attribute;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateAttributeWithValues(Attribute $attribute, array $attributeData, ?array $options = null, ?array $descriptions = null, ?array $valueDescriptions = null): Attribute
    {
        DB::beginTransaction();
        try {
            // Extract descriptions from attributeData
            $descriptions = $descriptions ?? [];

            // Remove translatable fields from attributeData
            unset($attributeData['name'], $attributeData['input_placeholder']);

            // Update the attribute (without translatable fields)
            $attribute->update($attributeData);

            // Update or create descriptions for each language
            foreach ($descriptions as $langId => $descriptionData) {
                AttributeDescription::updateOrCreate(
                    [
                        'attribute_id' => $attribute->id,
                        'language_id' => $descriptionData['language_id'],
                    ],
                    [
                        'name' => $descriptionData['name'],
                        'input_placeholder' => $descriptionData['input_placeholder'] ?? null,
                    ]
                );
            }

            // Update or create attribute values if it has predefined value descriptions
            if (in_array($attributeData['display_type_user_panel'], ListingAttributeDisplayTypeConstant::getPredefinedValueTypes()) && is_array($valueDescriptions) && !empty($valueDescriptions)) {
                // Get existing value IDs to track which ones to keep
                $existingValueIds = $attribute->values->pluck('id')->toArray();
                $newValueIds = [];

                // Update or create values
                foreach ($valueDescriptions as $index => $valueData) {
                    // Check if this is an existing value (has id in the data)
                    $valueId = $valueData['id'] ?? null;
                    $attributeValue = null;

                    if ($valueId && in_array($valueId, $existingValueIds)) {
                        // Update existing value
                        $attributeValue = AttributeValue::find($valueId);
                        $attributeValue->update(['order' => $index]);
                        $newValueIds[] = $valueId;
                    } else {
                        // Create new value
                        $attributeValue = AttributeValue::create([
                            'attribute_id' => $attribute->id,
                            'order' => $index,
                        ]);
                        $newValueIds[] = $attributeValue->id;
                    }

                    // Update or create value descriptions for each language
                    // valueData structure: ['id' => X, language_id => ['language_id' => Y, 'value' => Z]]
                    if (is_array($valueData)) {
                        foreach ($valueData as $key => $langValueData) {
                            // Skip 'id' key
                            if ($key === 'id') {
                                continue;
                            }

                            if (is_array($langValueData) && isset($langValueData['language_id']) && isset($langValueData['value'])) {
                                AttributeValueDescription::updateOrCreate(
                                    [
                                        'attribute_value_id' => $attributeValue->id,
                                        'language_id' => $langValueData['language_id'],
                                    ],
                                    [
                                        'value' => $langValueData['value'],
                                    ]
                                );
                            }
                        }
                    }
                }

                // Delete values that are no longer in the list
                AttributeValue::where('attribute_id', $attribute->id)
                    ->whereNotIn('id', $newValueIds)
                    ->delete();
            } else {
                // If display type changed from select/checkbox/radio, delete all values
                foreach ($attribute->values as $value) {
                    $value->descriptions()->delete();
                    $value->delete();
                }
            }

            DB::commit();
            return $attribute;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get all parent categories with their children.
     */
    public function getParentCategoriesWithChildren(): Collection
    {
        return app(Category::class)->whereNull('parent_id')->with('children')->get();
    }

    /**
     * Get all attributes in the system.
     */
    public function getAllAttributes(): Collection
    {
        return $this->model->all();
    }

    /**
     * Sync attributes with a category with position data.
     */
    public function syncCategoryAttributes(Category $category, array $attributeData): bool
    {
        $category->attributes()->sync($attributeData);
        return true;
    }
}
