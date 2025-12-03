<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TourClaim extends Model
{
    protected $guarded = [];

    protected $casts = [
        'dep_datetime'              => 'datetime',
        'arr_datetime'              => 'datetime',
        'submitted_at'              => 'datetime',
        'closed_at'                 => 'datetime',
        'advance_inr'               => 'decimal:2',
        'advance_forex'             => 'decimal:2',
        'total_expenses_inr'        => 'decimal:2',
        'total_expenses_forex'      => 'decimal:2',
        'amount_reimburse_inr'      => 'decimal:2',
        'amount_reimburse_forex'    => 'decimal:2',
        'amount_refund_inr'         => 'decimal:2',
        'amount_refund_forex'       => 'decimal:2',
        'payload_json'              => 'array',

        // New Cast
        'file_history'              => 'array',
    ];

    protected static function booted(): void
    {
        // When creating a new tour claim
        static::creating(function ($model) {
            // 1. Ensure employee_id is set (from logged-in user) – keep your existing logic
            if (empty($model->employee_id)) {
                $model->employee_id = optional(Auth::user()?->employee)->id;
            }

            // If still no employee_id, stop cleanly
            if (empty($model->employee_id)) {
                throw ValidationException::withMessages([
                    'employee_id' => 'Your user account is not linked to any employee profile.',
                ]);
            }

            // 2. Auto-generate application number IF not already set
            if (empty($model->application_no)) {
                // Load the employee to access emp_id
                $employee = $model->employee ?? \App\Models\Employee::find($model->employee_id);

                // Derive 4-digit employee code from emp_id
                $empCodeRaw = $employee?->employee_code ?? '';
                $empDigits = preg_replace('/\D/', '', $empCodeRaw) ?: '0'; // keep only digits, fallback to "0"
                $empDigitsPadded = str_pad($empDigits, 4, '0', STR_PAD_LEFT);
                $empCode = substr($empDigitsPadded, -4); // last 4 digits

                // Find last application_no for this employee
                $lastAppNo = static::where('employee_id', $model->employee_id)
                    ->whereNotNull('application_no')
                    ->orderByDesc('id')
                    ->value('application_no');

                $lastSeq = 0;
                if (! empty($lastAppNo)) {
                    // Take last 6 characters as the numeric sequence
                    $lastSeqPart = substr($lastAppNo, -6);
                    if (ctype_digit($lastSeqPart)) {
                        $lastSeq = (int) $lastSeqPart;
                    }
                }

                $nextSeq = $lastSeq + 1;
                $seqPart = str_pad((string) $nextSeq, 6, '0', STR_PAD_LEFT);

                // Final 10-digit application number: 4-digit emp + 6-digit seq
                $model->application_no = $empCode . $seqPart;
            }
        });

        // Prevent changing application_no after it has been set
        static::updating(function ($model) {
            if ($model->isDirty('application_no')) {
                $model->application_no = $model->getOriginal('application_no');
            }
        });
    }

    // Relationship to the person holding the claim
    public function pendingWith(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'pending_with');
    }

    // Helper to check access
    public function canBeActionedBy(User $user): bool
    {
        return $this->pending_with === $user->employee?->id;
    }

    public function items(): HasMany
    {
        return $this->hasMany(TourClaimItem::class, 'tour_claim_id');
    }

    // if you need employee relation for scoping
    public function employee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Employee::class, 'employee_id');
    }

    // tiny helpers for the form’s split date+time fields
    public function getTourStartDateAttribute(): ?string
    {
        return $this->dep_datetime?->toDateString();
    }
    public function getTourStartTimeAttribute(): ?string
    {
        return $this->dep_datetime?->format('H:i');
    }
    public function getTourEndDateAttribute(): ?string
    {
        return $this->arr_datetime?->toDateString();
    }
    public function getTourEndTimeAttribute(): ?string
    {
        return $this->arr_datetime?->format('H:i');
    }

    public function setTourStartDateAttribute($value): void
    {
        if ($value) {
            $time = $this->tour_start_time ?? '00:00';
            $this->dep_datetime = Carbon::parse("{$value} {$time}");
        }
    }
    public function setTourStartTimeAttribute($value): void
    {
        if ($value) {
            $date = $this->tour_start_date ?? now()->toDateString();
            $this->dep_datetime = Carbon::parse("{$date} {$value}");
        }
    }
    public function setTourEndDateAttribute($value): void
    {
        if ($value) {
            $time = $this->tour_end_time ?? '00:00';
            $this->arr_datetime = Carbon::parse("{$value} {$time}");
        }
    }
    public function setTourEndTimeAttribute($value): void
    {
        if ($value) {
            $date = $this->tour_end_date ?? now()->toDateString();
            $this->arr_datetime = Carbon::parse("{$date} {$value}");
        }
    }
}
