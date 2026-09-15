<?php

namespace App\Http\Requests\Admin\Attribute;

use Illuminate\Foundation\Http\FormRequest;

class AssignToCategoryRequest extends FormRequest
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
        return [
            'attributes' => 'nullable|array',
            'attributes.*' => 'exists:attributes,id',
            'positions' => 'nullable|array',
            'positions.*' => 'in:top,bottom',
        ];
    }

    /**
     * Get the attribute data with positions from the request.
     */
    public function getAttributeData(): array
    {
        $validatedData = $this->validated();
        $attributes = $validatedData['attributes'] ?? [];
        $positions = $validatedData['positions'] ?? [];

        $attributeData = [];
        foreach ($attributes as $attributeId) {
            $attributeData[$attributeId] = [
                'order' => 0, // Default order
                'property_detail_position' => $positions[$attributeId] ?? 'bottom'
            ];
        }

        return $attributeData;
    }

    /**
     * Get the attribute IDs from the request.
     * @deprecated Use getAttributeData() instead
     */
    public function getAttributeIds(): array
    {
        $validatedData = $this->validated();
        return $validatedData['attributes'] ?? [];
    }
}
