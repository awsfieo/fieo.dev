<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use SoftDeletes; 
    
    protected $fillable = [
        'sort_id',
        'office',
        'address',
        'city',
        'state',
        'pin',
        'email',
        'phone',
        'fax',
        'country',
        'latitude',
        'longitude',
        'parent_id',
        'is_active',
    ];

    protected $casts = [
        'sort_id'   => 'integer',
        'parent_id' => 'integer',
        'is_active' => 'boolean',
        'latitude'  => 'float',
        'longitude' => 'float',
    ];
}
