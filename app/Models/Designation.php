<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designation extends Model
{
    use SoftDeletes; 

    protected $fillable = [
        'sort_id',
        'designation',
        'description',
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
