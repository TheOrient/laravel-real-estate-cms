<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeValueDescription extends Model
{
    protected $fillable = [
        'attribute_value_id',
        'language_id',
        'value',
    ];

    /**
     * Get the attribute value that owns the description
     */
    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }

    /**
     * Get the language that owns the description
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
