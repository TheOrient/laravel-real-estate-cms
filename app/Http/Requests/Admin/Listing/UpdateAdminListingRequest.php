<?php

namespace App\Http\Requests\Admin\Listing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminListingRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'required|exists:districts,id',
            'neighborhood_id' => 'nullable|exists:neighborhoods,id',
            'is_approved' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean'
        ];
    }
}
