<?php

namespace App\Http\Requests\Admin\User;

use App\Constants\UserRolesConstant;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:2000',
            'enable_whatsapp' => 'required|in:yes,no',
            'user_role' => 'required|in:'.implode(',', UserRolesConstant::getAllRoles()),
            'is_active' => 'boolean',
            'email_verified' => 'boolean',
        ];
    }

    /**
     * Get the validated data with added values.
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();

        // Set defaults for optional fields
        $validated['is_active'] = $this->has('is_active');
        $validated['version'] = 1;

        // Set email_verified_at if checkbox is checked
        if ($this->has('email_verified')) {
            $validated['email_verified_at'] = now();
        }
        unset($validated['email_verified']); // Remove the checkbox value

        return $validated;
    }
}
