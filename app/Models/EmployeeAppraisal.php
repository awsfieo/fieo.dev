<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

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

    /**
     * Check if the appraisal submission window is currently open for this employee.
     */
    public function isSubmissionWindowOpen(): bool
    {
        $today = Carbon::today();

        // 1. Check Standard Window
        // Since 'date' cast is used, these are already Carbon instances or null
        if ($this->appraisal_start_date && $this->appraisal_end_date) {
            if ($today->betweenIncluded($this->appraisal_start_date, $this->appraisal_end_date)) {
                return true;
            }
        }

        // 2. Check Extension Window
        if ($this->deadline_extension && $this->deadline_extension_date) {
            if ($today->lessThanOrEqualTo($this->deadline_extension_date)) {
                return true;
            }
        }

        return false;
    }
    
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function appraisal(): BelongsTo
    {
        return $this->belongsTo(Appraisal::class);
    }
}
