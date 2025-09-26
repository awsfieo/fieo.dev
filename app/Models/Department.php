<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes; 

    protected $fillable = [
        'sort_id',
        'department',
        'description',
        'short_title',
        'type',      // HO | Department | Region | Chapter | Office
        'gstin',
        'mid',
        'url',
        'parent_id',
        'office_id',
        'is_active',
    ];

    protected $casts = [
        'sort_id'   => 'integer',
        'parent_id' => 'integer',
        'office_id' => 'integer',
        'is_active' => 'boolean',
    ];
}
