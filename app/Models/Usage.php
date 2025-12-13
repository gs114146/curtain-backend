<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usage extends Model
{
    protected $table = 'usage';

    protected $fillable = [
        'name',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    /**
     * Get the products for this usage.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
