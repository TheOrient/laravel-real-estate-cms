<?php

namespace App\Constants;

class ListingAttributeDisplayTypeConstant
{
    // Select box with only one selection allowed
    const SELECT = 'select';
    // Select2 with multiple selections
    const MULTISELECT = 'multi_select';
    // input number insertion
    const NUMBER = 'number';
    // Checkbox with multiple selections
    const CHECKBOX = 'checkbox';
    // Radio button with single selection
    const RADIO = 'radio';
    // Free text input
    const INPUT = 'input';
    // Multi-line text input(dont recommended if you dont have to)
    const TEXTAREA = 'textarea';
    // Get all types as an array
    public static function getAllTypes()
    {
        return [
            self::SELECT, // singular
            self::MULTISELECT, // multiple
            self::NUMBER, // singular | free text input
            self::CHECKBOX, // multiple
            self::RADIO, // singular
            self::INPUT, // free text input
            self::TEXTAREA, // free text input
        ];
    }
    // Get types that has free text input
    public static function getFreeTextInputTypes()
    {
        return [
            self::INPUT,
            self::TEXTAREA,
            self::NUMBER,
        ];
    }
    // Get types that has predefined values
    public static function getPredefinedValueTypes()
    {
        return [
            self::SELECT,
            self::MULTISELECT,
            self::CHECKBOX,
            self::RADIO,
        ];
    }
    // get singular pre-defined value types
    public static function getSingularPredefinedValueTypes()
    {
        return [
            self::SELECT,
            self::RADIO,
        ];
    }
    // get multiple pre-defined value types
    public static function getMultiplePredefinedValueTypes()
    {
        return [
            self::MULTISELECT,
            self::CHECKBOX,
        ];
    }
}
