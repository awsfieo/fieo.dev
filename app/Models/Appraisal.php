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
        'evaluation_form_data'          => 'encrypted:array',
        'regional_head_review_data'     => 'encrypted:array',
        'final_assessment_data'         => 'encrypted:array',
        'final_increment'               => 'encrypted',
        'file_history'                  => 'array',
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

            // 2. Capture Snapshot Data (FIXED LOGIC)
            if ($model->employee_id) {
                $employee = Employee::find($model->employee_id);

                // Always ensure employee_code is set
                if (empty($model->employee_code)) {
                    $model->employee_code = $employee?->employee_code;
                }

                // Always capture these snapshots when creating, regardless of employee_code presence
                $model->designation_id = $model->designation_id ?? $employee?->designation_id;
                $model->department_id  = $model->department_id ?? $employee?->department_id;
                $model->office_id      = $model->office_id ?? $employee?->office_id;
                $model->basic          = $model->basic ?? $employee?->basic;
            }

            // 3. Generate Application No
            if (empty($model->application_no)) {
                $year = $model->appraisal_year ?? date('Y');
                $month = strtoupper(substr($model->appraisal_month ?? 'APR', 0, 3));

                // Fallback if employee code is still somehow missing
                $empSuffix = $model->employee_code
                    ? str_pad(substr($model->employee_code, -4), 4, '0', STR_PAD_LEFT)
                    : 'TMP-' . rand(1000, 9999);

                // Format: APR/2025/APR/0052
                $model->application_no = "APR/{$year}/{$month}/{$empSuffix}";
            }
        });

        // --- ADD THIS BLOCK IMMEDIATELY AFTER static::creating ---
        static::created(function ($model) {
            // Find the corresponding HR record (EmployeeAppraisal)
            $empAppraisal = \App\Models\EmployeeAppraisal::query()
                ->where('employee_code', $model->employee_code)
                ->where('appraisal_year', $model->appraisal_year)
                ->where('appraisal_month', $model->appraisal_month)
                ->first();

            // Link them together
            if ($empAppraisal) {
                $empAppraisal->update(['appraisal_id' => $model->id]);
            }
        });
    }

    /**
     * Centralized logic to calculate the average competency score.
     */
    public static function calculateCompetencyScore(array $ratings): float
    {
        $keys = [
            'knowledge',
            'verbal_skills',
            'written_skills',
            'computer_skills',
            'teamwork',
            'discipline',
            'obedience',
            'planning',
            'responsibility',
            'adaptability'
            // 'relationships', 'leadership' // Uncomment if you enable these later
        ];

        $sum = 0;
        $count = 0;

        foreach ($keys as $key) {
            if (isset($ratings[$key]) && is_numeric($ratings[$key])) {
                $sum += $ratings[$key];
                $count++;
            }
        }

        return $count > 0 ? round($sum / $count, 2) : 0.0;
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
            'final_assessment_pending' => 'primary',
            'closed' => 'success',
            default => 'gray',
        };
    }
}
