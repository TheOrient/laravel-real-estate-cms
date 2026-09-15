<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogDescription extends Model
{
    protected $fillable = [
        'blog_id',
        'language_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
