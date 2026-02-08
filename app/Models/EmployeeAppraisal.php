<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAppraisal extends Model
{
    protected $guarded = [];

    protected $casts = [
        'increment_granted' => 'boolean',
        'appraisal_start_date'    => 'date',
        'appraisal_end_date'      => 'date',
        
        // UPDATED CASTS
        'deadline_extension'      => 'boolean', // Now a boolean flag
        'deadline_extension_date' => 'date',    // Now a date object
        
        'increment_percentage'    => 'encrypted',
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
