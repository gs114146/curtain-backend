<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMedia extends Model
{
    protected $fillable = [
        'product_id',
        'variant_id',
        'file_path',
        'file_name',
        'thumbnail',
        'file_type',
        'mime_type',
        'file_size',
        'sort_order',
        'is_primary',
        'alt_text',
        'caption',
        'status'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
        'status' => 'string'
    ];

    /**
     * Get the product that owns the media.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant that owns the media (if variant-specific).
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
