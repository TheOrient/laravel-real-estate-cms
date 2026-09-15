<?php

namespace App\Http\Requests\Admin\User;

use App\Constants\UserRolesConstant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only super admin (ID 1) can edit themselves
        $targetUser = $this->route('user');
        if ($targetUser && $targetUser->id === 1 && auth()->id() !== 1) {
            return false;
        }

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
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->route('user')),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:2000',
            'enable_whatsapp' => 'required|in:yes,no',
            'user_role' => 'required|in:'.implode(',', UserRolesConstant::getAllRoles()),
            'is_active' => 'boolean',
            'email_verified' => 'boolean',
        ];
    }

    /**
     * Get the validated data for an update.
     */
    public function validatedForUpdate(): array
    {
        $validated = $this->validated();
        $targetUser = $this->route('user');

        // Additional protection: If target is super admin (ID 1) and current user is not super admin
        // This should not happen due to authorize(), but extra safety
        if ($targetUser && $targetUser->id === 1 && auth()->id() !== 1) {
            abort(403, __('admin/users.cannot_edit_super_admin'));
        }

        // Prevent changing super admin (ID 1) role - even by themselves
        if ($targetUser && $targetUser->id === 1) {
            $validated['user_role'] = UserRolesConstant::ADMIN; // Force admin role
        }

        // Remove empty password if not being updated
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        // Set is_active from checkbox
        $validated['is_active'] = $this->has('is_active');

        // Handle email_verified checkbox
        if ($this->has('email_verified')) {
            // If checked and wasn't verified before, set current timestamp
            if (!$this->route('user')->hasVerifiedEmail()) {
                $validated['email_verified_at'] = now();
            }
        } else {
            // If unchecked, remove verification
            $validated['email_verified_at'] = null;
        }
        unset($validated['email_verified']); // Remove the checkbox value

        return $validated;
    }
}
