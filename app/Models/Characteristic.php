<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Characteristic extends Model
{
    protected $fillable = [
        'name',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    /**
     * Get the products for this characteristic.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
