<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    protected $fillable = [
        'city_id',
        'name',
        'slug',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get the city that owns the district
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the neighborhoods for the district
     */
    public function neighborhoods(): HasMany
    {
        return $this->hasMany(Neighborhood::class);
    }

    /**
     * Get the listings for the district
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'district_id');
    }
}
