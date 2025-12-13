<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'color_id',
        'article',
        'article_description',
        'sku',
        'price',
        'sale_price',
        'stock_quantity',
        'status',
        'in_stock',
        'is_default',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'in_stock' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
        'status' => 'string'
    ];

    /**
     * Get the product that owns the variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the color for this variant.
     */
    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    /**
     * Get the media for this variant.
     */
    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class, 'variant_id');
    }

    /**
     * Get the primary media for this variant.
     */
    public function primaryMedia(): HasMany
    {
        return $this->hasMany(ProductMedia::class, 'variant_id')->where('is_primary', true);
    }

    /**
     * Scope to get active variants only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get default variant.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get variants in stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('in_stock', true);
    }
}
