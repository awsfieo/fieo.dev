<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
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

    public function departments(): HasMany
    {
        return $this->hasMany(\App\Models\Department::class, 'office_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}