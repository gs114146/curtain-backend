<?php

namespace App\Models;

use App\Models\Concerns\HasTitleCaseName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usage extends Model
{
    use HasTitleCaseName;

    protected $table = 'usage';

    protected $fillable = [
        'external_id',
        'name',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    /**
     * Get the products for this usage (legacy single relationship via usage_id).
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the products that belong to this usage (many-to-many).
     */
    public function productsMany(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_usage');
    }
}
