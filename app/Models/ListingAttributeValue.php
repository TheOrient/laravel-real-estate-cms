<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingAttributeValue extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attribute_value_listing';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'attribute_value_id',
        'listing_id',
        'custom_value',
    ];

    /**
     * Get the listing that owns this attribute value.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Get the attribute value associated with this record.
     */
    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }

    /**
     * Get the attribute through the attribute value.
     */
    public function getAttributeModel()
    {
        return $this->attributeValue->attribute;
    }
}
