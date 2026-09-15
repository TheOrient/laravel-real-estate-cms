<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageDescription extends Model
{
    protected $fillable = [
        'page_id',
        'language_id',
        'slug',
        'meta_description',
        'content',
        'title',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
