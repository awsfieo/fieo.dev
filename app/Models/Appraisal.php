<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Appraisal extends Model
{
    protected $guarded = [];

    protected $casts = [
        // Encryption
        'appraisal_form_data'           => 'encrypted:array',
        'common_evaluation_data'        => 'encrypted:array',
        'regional_head_assessment_data' => 'encrypted:array',
        'final_assessment_data'         => 'encrypted:array',
        'final_increment'               => 'encrypted',

        // Standard
        'file_history'                   => 'array',
        'deadline_extension'             => 'date',
        'appraisal_start_date'           => 'date',
        'appraisal_end_date'             => 'date',
        'emp_granted_deadline_extension' => 'array', // Fixed cast to array
        'is_released'                    => 'boolean',
        'basic'                          => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $user = Auth::user();
            $employee = $user?->employee;

            // 1. Auto-fill Employee ID & Snapshot Details
            if (empty($model->employee_id) && $employee) {
                $model->employee_id   = $employee->id;
                $model->employee_code = $employee->employee_code;
                
                // SNAPSHOTS: Capture current details
                $model->designation_id = $employee->designation_id;
                $model->department_id  = $employee->department_id;
                $model->office_id      = $employee->office_id;
                $model->basic          = $employee->basic;
            }

            // 2. Auto-generate Application No
            if (empty($model->application_no) && $model->employee_code) {
                $year = $model->appraisal_year ?? date('Y');
                $cycle = strtoupper(substr($model->appraisal_cycle ?? 'APR', 0, 3));
                $empSuffix = str_pad(substr($model->employee_code, -4), 4, '0', STR_PAD_LEFT);
                
                $model->application_no = "APR/{$year}/{$cycle}/{$empSuffix}";
            }
        });
    }

    // --- Relationships ---

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function pendingWith(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'pending_with', 'employee_code');
    }
    
    // Snapshot Relationships (Optional, if you want to see what the designation WAS at that time)
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'submitted' => 'warning',
            'evaluation_pending' => 'warning',
            'regional_head_review_pending' => 'orange',
            'final_review_pending' => 'primary',
            'closed' => 'success',
            default => 'gray',
        };
    }
}