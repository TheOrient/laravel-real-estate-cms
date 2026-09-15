<?php

namespace App\Http\Requests\Admin\Language;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && method_exists($this->user(), 'isAdmin')
            ? $this->user()->isAdmin()
            : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Admin panelde başlık, aktiflik ve sıralama güncellenebilir
            'title' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'is_default' => 'sometimes|boolean',
        ];
    }
}
