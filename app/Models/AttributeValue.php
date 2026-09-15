<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttributeValue extends Model
{
    protected $fillable = [
        'attribute_id',
        'order',
    ];

    /**
     * Get the attribute that owns this value
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get the listings that have this attribute value
     */
    public function listings(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'attribute_value_listing')
                    ->withPivot('custom_value')
                    ->withTimestamps();
    }

    /**
     * Get all descriptions for this attribute value
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(AttributeValueDescription::class);
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
     * Get attribute value (accessor for backward compatibility)
     */
    public function getValueAttribute()
    {
        $description = $this->getCurrentDescription();
        return $description ? $description->value : 'Value #' . $this->id;
    }
}
