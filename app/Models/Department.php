<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    // Type codes: HO = Head Office, DO = Department, RO = Regional Office, CO = Chapter Office
    public const TYPE_HO = 'HO';
    public const TYPE_DO = 'DO';
    public const TYPE_RO = 'RO';
    public const TYPE_CO = 'CO';
    
    public const REGION_HO = 'HO';
    public const REGION_NR = 'NR';
    public const REGION_ER = 'ER';
    public const REGION_WR = 'WR';
    public const REGION_SR = 'SR';

    public const TYPE_LABELS = [
        self::TYPE_HO => 'Head Office',
        self::TYPE_DO => 'Department',
        self::TYPE_RO => 'Regional Office',
        self::TYPE_CO => 'Chapter Office',
    ];

    public const REGION_LABELS = [
        self::REGION_HO => 'Head Office',
        self::REGION_NR => 'Northern Region',
        self::REGION_ER => 'Eastern Region',
        self::REGION_WR => 'Western Region',
        self::REGION_SR => 'Southern Region',
    ];

    protected $fillable = [
        'sort_id',
        'department',
        'long_title',   // <-- matches migration
        'short_title',
        'type',         // HO | DO | RO | CO
        'region', // NR, ER, WR, SR
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
        'type'      => 'string',
    ];

    /** Self reference: parent department */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** Self reference: child departments */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** Link to offices table */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    /** Convenience label for Filament / views */
    public function getTypeLabelAttribute(): ?string
    {
        return self::TYPE_LABELS[$this->type] ?? null;
    }

    /** Common scope */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Backwards-compat (only if old code still reads `description`):
     * Maps description -> long_title
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->long_title;
    }

    public function setDescriptionAttribute(?string $value): void
    {
        $this->attributes['long_title'] = $value;
    }
}
