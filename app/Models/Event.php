<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Event extends Model implements HasMedia
{
    use InteractsWithMedia;
    use LogsActivity;

    // ✅ ADD THIS LINE TO FIX THE ERROR
    protected $guarded = [];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'listing_start_at' => 'datetime',
        'listing_end_at' => 'datetime',
        'published_at' => 'datetime',
        'registration_charges_json' => 'array',
        'add_home_page_ticker' => 'boolean',
        'allow_registration' => 'boolean',
        'allow_partial_payment' => 'boolean',
        'tds_deducted' => 'boolean',
        'under_mai_scheme' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banners')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('attachments')
            ->useDisk('public');
    }
    
    // Relationship for Created By (used in Table)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}