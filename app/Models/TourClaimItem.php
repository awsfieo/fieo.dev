<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourClaimItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'period_from'   => 'date',
        'period_to'     => 'date',
        'exchange_rate' => 'decimal:6',
        'amount_forex'  => 'decimal:2',
        'amount_inr'    => 'decimal:2',
        'payload_json'  => 'array',
        'uploads'       => 'array',   // ← IMPORTANT NEW CAST
    ];

    protected static function booted(): void
    {
        static::saved(function (TourClaimItem $item): void {
            // Use the uploads array coming from FileUpload
            $uploads = $item->uploads ?? [];

            if (! is_array($uploads) || empty($uploads)) {
                return;
            }

            // Clear existing files for this item (on edit)
            $item->claimFiles()->delete();

            foreach ($uploads as $path) {
                if (! $path) {
                    continue;
                }

                $item->claimFiles()->create([
                    'kind'          => 'bill',
                    'disk'          => 'public',
                    'path'          => $path,
                    'original_name' => basename($path),
                    'mime'          => null,
                    'size'          => null,
                ]);
            }
        });
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(TourClaim::class, 'tour_claim_id');
    }

    public function claimFiles(): HasMany
    {
        return $this->hasMany(TourClaimFile::class, 'tour_claim_item_id');
    }
}
