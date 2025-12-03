<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{

    protected $fillable = [
        'sort_id',
        'designation',
        'long_title',
        'short_title',
        'seniority',
        'is_officer',
        'is_active',
    ];

    protected $casts = [
        'sort_id'    => 'integer',
        'seniority'  => 'integer',
        'is_officer' => 'boolean',
        'is_active'  => 'boolean',
    ];
}
