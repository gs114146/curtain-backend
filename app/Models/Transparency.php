<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transparency extends Model
{
    protected $table = 'transparency';

    protected $fillable = [
        'name',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    /**
     * Get the products for this transparency.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
