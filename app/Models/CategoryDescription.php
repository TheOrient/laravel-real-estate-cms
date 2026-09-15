<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryDescription extends Model
{
    protected $fillable = [
        'category_id',
        'language_id',
        'name',
        'slug',
        'short_description',
        'description',
    ];

    /**
     * Get the category that owns the description
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the language that owns the description
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
