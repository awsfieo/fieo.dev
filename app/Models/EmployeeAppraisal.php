<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAppraisal extends Model
{
    protected $guarded = [];

    protected $casts = [
        'increment_granted' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function appraisal(): BelongsTo
    {
        return $this->belongsTo(Appraisal::class);
    }
}
