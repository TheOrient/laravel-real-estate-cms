<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get the districts for the city
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    /**
     * Get the listings for the city
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'city_id');
    }
}
