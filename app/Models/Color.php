<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends Model
{
    protected $fillable = [
        'name',
        'color_value',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    /**
     * Get the products for this color.
     * Note: This is kept for backward compatibility. New products use variants.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the product variants for this color.
     */
    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
