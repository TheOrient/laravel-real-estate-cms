<?php

namespace App\Rules;

use App\Helpers\PhoneHelper;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TurkishPhoneRule implements ValidationRule
{
    protected ?int $ignoreUserId;
    protected bool $checkUnique;

    /**
     * Create a new rule instance.
     *
     * @param bool $checkUnique Whether to check if phone is unique in database
     * @param int|null $ignoreUserId User ID to ignore in uniqueness check (for updates)
     */
    public function __construct(bool $checkUnique = true, ?int $ignoreUserId = null)
    {
        $this->checkUnique = $checkUnique;
        $this->ignoreUserId = $ignoreUserId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if value is empty
        if (empty($value)) {
            $fail('Telefon numarası zorunludur.');
            return;
        }

        // Check format: +90 5XX XXX XX XX
        if (!preg_match('/^\+90\s5\d{2}\s\d{3}\s\d{2}\s\d{2}$/', $value)) {
            $fail('Geçerli bir Türkiye telefon numarası giriniz. Örnek: +90 5XX XXX XX XX');
            return;
        }

        // Check uniqueness if required
        if ($this->checkUnique) {
            $sanitizedPhone = PhoneHelper::sanitize($value);

            $query = User::where('phone', $sanitizedPhone);

            // Ignore specific user ID if provided (useful for profile updates)
            if ($this->ignoreUserId) {
                $query->where('id', '!=', $this->ignoreUserId);
            }

            if ($query->exists()) {
                $fail('Bu telefon numarası zaten kayıtlı.');
            }
        }
    }
}
