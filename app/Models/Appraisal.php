<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Appraisal extends Model
{
    protected $guarded = [];

    protected $casts = [
        'appraisal_form_data'           => 'encrypted:array',
        'common_evaluation_data'        => 'encrypted:array',
        'regional_head_assessment_data' => 'encrypted:array',
        'final_assessment_data'         => 'encrypted:array',
        'final_increment'               => 'encrypted',
        'file_history'                  => 'array',
        'deadline_extension'            => 'date',
        'appraisal_start_date'          => 'date',
        'appraisal_end_date'            => 'date',
        'emp_granted_deadline_extension'=> 'array',
        'is_released'                   => 'boolean',
        'basic'                         => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            // 1. Ensure Employee ID is set
            if (empty($model->employee_id)) {
                $model->employee_id = Auth::user()?->employee?->id;
            }

            // 2. Ensure Employee Code is set (Vital for App No)
            if (empty($model->employee_code) && $model->employee_id) {
                // Fetch from relationship if not in payload
                $employee = Employee::find($model->employee_id);
                $model->employee_code = $employee?->employee_code;
                
                // Also fill snapshot data while we are at it
                $model->designation_id = $employee?->designation_id;
                $model->department_id  = $employee?->department_id;
                $model->office_id      = $employee?->office_id;
                $model->basic          = $employee?->basic;
            }

            // 3. Generate Application No
            if (empty($model->application_no)) {
                $year = $model->appraisal_year ?? date('Y');
                $cycle = strtoupper(substr($model->appraisal_cycle ?? 'APR', 0, 3));
                
                // Fallback if employee code is still somehow missing
                $empSuffix = $model->employee_code 
                    ? str_pad(substr($model->employee_code, -4), 4, '0', STR_PAD_LEFT)
                    : 'TMP-' . rand(1000, 9999);
                
                // Format: APR/2025/APR/0052
                $model->application_no = "APR/{$year}/{$cycle}/{$empSuffix}";
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function pendingWith(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'pending_with', 'employee_code');
    }

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