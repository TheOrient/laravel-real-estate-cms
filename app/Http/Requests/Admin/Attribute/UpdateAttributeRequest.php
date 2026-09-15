<?php

namespace App\Http\Requests\Admin\Attribute;

use App\Constants\ListingAttributeDisplayTypeConstant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttributeRequest extends FormRequest
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
            'descriptions.*.input_placeholder' => 'nullable|string|max:255',
            'display_type_search' => ['required', Rule::in(ListingAttributeDisplayTypeConstant::getAllTypes())],
            'display_type_user_panel' => ['required', Rule::in(ListingAttributeDisplayTypeConstant::getAllTypes())],
            'is_filterable' => 'boolean',
            'show_in_listing' => 'boolean',
            'is_required' => 'boolean',
            'allow_multiple' => 'boolean',
            'min_value' => 'nullable|integer',
            'max_value' => 'nullable|integer',
            'options' => 'nullable|array',
            'value_descriptions' => 'nullable|array',
            'value_descriptions.*' => 'nullable|array',
            'value_descriptions.*.*.language_id' => 'required_with:value_descriptions|exists:languages,id',
            'value_descriptions.*.*.value' => 'required_with:value_descriptions|string|max:255',
        ];

        return $rules;
    }

    /**
     * Validate and prepare attribute data for update.
     */
    public function validateAndPrepare(): array
    {
        $validated = $this->validated();

        // Extract descriptions
        $descriptions = $validated['descriptions'] ?? [];
        unset($validated['descriptions']);

        // Extract value descriptions
        $valueDescriptions = $validated['value_descriptions'] ?? [];
        unset($validated['value_descriptions']);

        // Set boolean values from checkboxes
        $validated['is_filterable'] = $this->has('is_filterable');
        $validated['show_in_listing'] = $this->has('show_in_listing');
        $validated['is_required'] = $this->has('is_required');
        $validated['allow_multiple'] = $this->has('allow_multiple');

        // Extract options for separate handling
        $options = null;
        if (in_array($validated['display_type_user_panel'], ['select', 'multi_select', 'checkbox', 'radio']) && !empty($validated['options'])) {
            $options = array_filter($validated['options']);
        }
        unset($validated['options']);

        // Validate min/max values
        if ($validated['display_type_user_panel'] === 'number' && $validated['min_value'] !== null && $validated['max_value'] !== null) {
            if ($validated['min_value'] > $validated['max_value']) {
                throw new \Exception(__('Minimum value cannot be greater than maximum value'));
            }
        }

        return [
            'attributeData' => $validated,
            'options' => $options,
            'descriptions' => $descriptions,
            'valueDescriptions' => $valueDescriptions,
        ];
    }
}
