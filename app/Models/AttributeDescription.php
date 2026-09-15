<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeDescription extends Model
{
    protected $fillable = [
        'attribute_id',
        'language_id',
        'name',
        'input_placeholder',
    ];

    /**
     * Get the attribute that owns the description
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get the language that owns the description
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
