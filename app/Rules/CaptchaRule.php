<?php

namespace App\Rules;

use App\Services\CaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CaptchaRule implements ValidationRule
{
    protected string $formType;

    public function __construct(string $formType = 'contact')
    {
        $this->formType = $formType;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $captchaService = app(CaptchaService::class);

        if (!$captchaService->isEnabled($this->formType)) {
            return; // CAPTCHA not enabled for this form
        }

        if (empty($value)) {
            $fail('Lütfen robot olmadığınızı doğrulayın.');
            return;
        }

        if (!$captchaService->verify($value, $this->formType)) {
            $fail('CAPTCHA doğrulaması başarısız. Lütfen tekrar deneyin.');
        }
    }
}
