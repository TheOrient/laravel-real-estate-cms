<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Attribute extends Model
{
    protected $fillable = [
        'display_type_search',
        'display_type_user_panel',
        'is_filterable',
        'show_in_listing',
        'is_required',
        'allow_multiple',
        'min_value',
        'max_value',
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'show_in_listing' => 'boolean',
        'is_required' => 'boolean',
        'allow_multiple' => 'boolean',
        'min_value' => 'integer',
        'max_value' => 'integer',
    ];

    /**
     * Get the values for this attribute
     */
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    /**
     * Get the categories this attribute is assigned to
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->withPivot('order', 'property_detail_position')
            ->withTimestamps();
    }

    /**
     * Get the custom values for this attribute
     */
    public function customValues(): HasMany
    {
        return $this->hasMany(ListingAttributeCustomValue::class);
    }

    /**
     * Get all descriptions for this attribute
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(AttributeDescription::class);
    }

    /**
     * Get description for a specific language
     */
    public function getDescriptionForLanguage($languageId = null)
    {
        if ($languageId === null) {
            $languageId = Language::where('is_default', true)->value('id');
        }

        return $this->descriptions()->where('language_id', $languageId)->first();
    }

    /**
     * Get description for current locale
     */
    public function getCurrentDescription()
    {
        $languageCode = app()->getLocale();
        $language = Language::where('code', $languageCode)->first();

        if (!$language) {
            $language = Language::where('is_default', true)->first();
        }

        return $language ? $this->getDescriptionForLanguage($language->id) : null;
    }

    /**
     * Get attribute name (accessor for backward compatibility)
     */
    public function getNameAttribute()
    {
        $description = $this->getCurrentDescription();
        return $description ? $description->name : 'Attribute #' . $this->id;
    }

    /**
     * Get attribute input_placeholder (accessor for backward compatibility)
     */
    public function getInputPlaceholderAttribute()
    {
        $description = $this->getCurrentDescription();
        return $description ? $description->input_placeholder : null;
    }
}
