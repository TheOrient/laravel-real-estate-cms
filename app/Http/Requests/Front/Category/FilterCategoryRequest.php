<?php

namespace App\Http\Requests\Front\Category;

use Illuminate\Foundation\Http\FormRequest;

class FilterCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            's' => 'nullable|string|max:255', // Arama parametresi
            'city_id' => 'nullable|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'neighborhood_id' => 'nullable|exists:neighborhoods,id',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort_by' => 'nullable|in:created_at,price',
            'sort_direction' => 'nullable|in:asc,desc',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string',
            // Home-search additions: a parent category slug to scope to
            // (e.g. "daire", "villa") and a sale/rent intent that the
            // repository turns into a category-name prefix filter.
            'category_slug' => 'nullable|string|max:255',
            'type'          => 'nullable|in:satilik,kiralik',
            'user_id' => 'prohibited', // Prevent user_id manipulation
        ];
    }

    /**
     * Get the filters from the request.
     */
    public function filters(): array
    {
        $filters = $this->validated();

        // Process attribute filters (inputs with attribute_ prefix)
        foreach ($this->all() as $key => $value) {
            if (strpos($key, 'attribute_') === 0 && !empty($value)) {
                $attributeId = substr($key, 10); // Remove 'attribute_' prefix to get ID
                $filters[$key] = $value; // Keep original format for view
            }
        }

        return $filters;
    }
}
