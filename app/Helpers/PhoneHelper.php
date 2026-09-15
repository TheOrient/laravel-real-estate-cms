<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Sanitize phone number to database format (905XXXXXXXXX)
     * Removes all non-numeric characters and ensures it starts with 90
     *
     * @param string|null $phone
     * @return string|null
     */
    public static function sanitize(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-numeric characters
        $cleaned = preg_replace('/\D/', '', $phone);

        // If empty after cleaning, return null
        if (empty($cleaned)) {
            return null;
        }

        // Remove leading zeros
        $cleaned = ltrim($cleaned, '0');

        // If starts with 90, it's already in correct format
        if (str_starts_with($cleaned, '90')) {
            return $cleaned;
        }

        // If starts with 5 (Turkish mobile), add 90 prefix
        if (str_starts_with($cleaned, '5')) {
            return '90' . $cleaned;
        }

        // If it's 10 digits starting with 5, add 90
        if (strlen($cleaned) === 10 && str_starts_with($cleaned, '5')) {
            return '90' . $cleaned;
        }

        // Return as is if we can't determine format
        return $cleaned;
    }

    /**
     * Format phone number for display (+90 5XX XXX XX XX)
     *
     * @param string|null $phone
     * @return string|null
     */
    public static function format(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // First sanitize to ensure consistent format
        $sanitized = self::sanitize($phone);

        if (empty($sanitized)) {
            return null;
        }

        // Expected format: 905XXXXXXXXX (12 digits)
        if (strlen($sanitized) === 12 && str_starts_with($sanitized, '90')) {
            // Format: +90 5XX XXX XX XX
            return sprintf(
                '+90 %s %s %s %s',
                substr($sanitized, 2, 3),  // 5XX
                substr($sanitized, 5, 3),  // XXX
                substr($sanitized, 8, 2),  // XX
                substr($sanitized, 10, 2)  // XX
            );
        }

        // If not in expected format, return sanitized version
        return $sanitized;
    }

    /**
     * Validate Turkish phone number format
     *
     * @param string|null $phone
     * @return bool
     */
    public static function validate(?string $phone): bool
    {
        if (empty($phone)) {
            return false;
        }

        $sanitized = self::sanitize($phone);

        // Must be 12 digits starting with 90
        if (strlen($sanitized) !== 12 || !str_starts_with($sanitized, '90')) {
            return false;
        }

        // Third digit must be 5 (Turkish mobile numbers start with 5)
        if (substr($sanitized, 2, 1) !== '5') {
            return false;
        }

        return true;
    }

    /**
     * Get validation regex pattern for Turkish phone numbers
     *
     * @return string
     */
    public static function getValidationPattern(): string
    {
        // Matches: +90 5XX XXX XX XX or 0 5XX XXX XX XX or 5XX XXX XX XX
        return '/^(\+?90|0)?5\d{2}\s?\d{3}\s?\d{2}\s?\d{2}$/';
    }
}
