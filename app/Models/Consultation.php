<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'preferred_date',
        'service_type',
        'additional_message',
        'status'
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'status' => 'string'
    ];
}
