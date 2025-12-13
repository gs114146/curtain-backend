<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'image',
        'description',
        'sku',
        'manual_product_id',
        'price',
        'sale_price',
        'stock_quantity',
        'dimensions',
        'weight',
        'horizontal_repeat',
        'vertical_repeat',
        'direction',
        'extra_data',
        'color_id',
        'design_id',
        'designs',
        'material_id',
        'compositions',
        'transparency_id',
        'characteristic_id',
        'usage_id',
        'hsn_code',
        'gst',
        'type',
        'status',
        'featured',
        'in_stock',
        'meta_title',
        'meta_description',
        'slug'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'gst' => 'decimal:2',
        'featured' => 'boolean',
        'in_stock' => 'boolean',
        'status' => 'string'
    ];

    /**
     * Get the color that owns the product.
     * Note: This is kept for backward compatibility. New products should use variants.
     */
    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    /**
     * Get the design that owns the product (legacy single relationship).
     */
    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }

    /**
     * Get the designs that belong to the product (many-to-many).
     */
    public function designs(): BelongsToMany
    {
        return $this->belongsToMany(Design::class, 'product_design');
    }

    /**
     * Get the material that owns the product (legacy single relationship).
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Get the materials that belong to the product (many-to-many).
     */
    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'product_material');
    }

    /**
     * Get the transparency that owns the product.
     */
    public function transparency(): BelongsTo
    {
        return $this->belongsTo(Transparency::class);
    }

    /**
     * Get the characteristic that owns the product (legacy single relationship).
     */
    public function characteristic(): BelongsTo
    {
        return $this->belongsTo(Characteristic::class);
    }

    /**
     * Get the characteristics that belong to the product (many-to-many).
     */
    public function characteristics(): BelongsToMany
    {
        return $this->belongsToMany(Characteristic::class, 'product_characteristic');
    }

    /**
     * Get the usage that owns the product.
     */
    public function usage(): BelongsTo
    {
        return $this->belongsTo(Usage::class);
    }

    /**
     * Get the variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /**
     * Get the active variants for the product.
     */
    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('status', 'active')->orderBy('sort_order');
    }

    /**
     * Get the default variant for the product.
     */
    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    /**
     * Get the media for the product (general media, not variant-specific).
     */
    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->whereNull('variant_id');
    }

    /**
     * Get the primary media for the product.
     */
    public function primaryMedia(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->whereNull('variant_id')->where('is_primary', true);
    }

    /**
     * Get all media including variant media.
     */
    public function allMedia(): HasMany
    {
        return $this->hasMany(ProductMedia::class);
    }
}
