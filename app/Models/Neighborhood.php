<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Neighborhood extends Model
{
    protected $fillable = [
        'district_id',
        'name',
        'slug',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get the district that owns the neighborhood
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the listings for the neighborhood
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'neighborhood_id');
    }
}
