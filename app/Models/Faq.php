<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'faq_category_id',
        'question',
        'answer',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Relationship with FAQ Category
     */
    public function category()
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }

    /**
     * Scope for active FAQs only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered FAQs
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('question', 'asc');
    }
}
