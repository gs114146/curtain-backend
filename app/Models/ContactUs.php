<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    protected $table = 'contact_us';

    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'project_type',
        'subject',
        'message',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];
}
