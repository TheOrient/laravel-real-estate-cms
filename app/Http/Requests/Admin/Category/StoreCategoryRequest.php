<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'descriptions' => 'required|array',
            'descriptions.*.language_id' => 'required|exists:languages,id',
            'descriptions.*.name' => 'required|string|max:255',
            'descriptions.*.short_description' => 'nullable|string|max:255',
            'descriptions.*.description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'sometimes|boolean',
            'icon' => 'nullable|string|max:50',
            'is_filterable' => 'boolean',
        ];

        return $rules;
    }

    /**
     * Get validated data with default values
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();

        // Set boolean fields to false if not present
        $validated['is_active'] = $this->has('is_active');
        $validated['is_filterable'] = $this->has('is_filterable');

        return $validated;
    }
}
