<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FaqCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Automatically generate slug from name if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Relationship with FAQs
     */
    public function faqs()
    {
        return $this->hasMany(Faq::class)->orderBy('sort_order', 'asc');
    }

    /**
     * Relationship with active FAQs only
     */
    public function activeFaqs()
    {
        return $this->hasMany(Faq::class)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc');
    }

    /**
     * Scope for active categories only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered categories
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }
}
