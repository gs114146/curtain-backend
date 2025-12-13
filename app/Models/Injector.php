<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Injector extends Model
{
    protected $fillable = [
        'part_number',
        'injector_number',
        'flow_2ms',
        'flow_4ms',
        'flow_6ms',
        'flow_9_95ms',
        'status',
        'batch_sequence',
    ];

    public function scopeInStock($query)
    {
        return $query->where('status', 'in_stock');
    }

    public function scopeOfType($query, $partNumber)
    {
        return $query->where('part_number', $partNumber);
    }
}
