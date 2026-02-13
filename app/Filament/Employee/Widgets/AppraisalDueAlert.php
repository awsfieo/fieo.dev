<?php

namespace App\Filament\Employee\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeAppraisal;
use App\Models\Appraisal;
use App\Filament\Employee\Resources\Appraisals\AppraisalResource;

class AppraisalDueAlert extends Widget
{
    protected string $view = 'filament.employee.widgets.appraisal-due-alert';

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 2;

    public ?EmployeeAppraisal $dueRecord = null;
    public ?string $actionUrl = null;
    public string $buttonLabel = 'Fill Appraisal Form';
    
    // New properties for the dynamic badge
    public string $badgeLabel = 'OPEN';
    public string $badgeColor = 'success'; // Green for Open, Orange/Red for Draft

    public static function canView(): bool
    {
        $user = Auth::user();
        if (!$user || !$user->employee) return false;

        // 1. Check if an Appraisal Cycle is currently OPEN in HR records
        $record = EmployeeAppraisal::query()
            ->where('employee_code', $user->employee->employee_code)
            ->where('status', 'Pending')
            ->get()
            ->filter(fn($r) => $r->isSubmissionWindowOpen())
            ->first();

        if (!$record) return false;

        // DG & CEO usually doesn't fill this form
        if ($user->hasRole('DG & CEO')) {
            return false;
        }

        // 2. Check if the user has already started the form
        $existingAppraisal = Appraisal::query()
            ->where('employee_code', $user->employee->employee_code)
            ->where('appraisal_year', $record->appraisal_year)
            ->where('appraisal_month', $record->appraisal_month)
            ->first();

        // 3. Logic: Hide ONLY if submitted (Status is NOT 'Draft')
        // If it is 'Draft', we return true (Show). 
        // If it is null (Not started), we return true (Show).
        if ($existingAppraisal && $existingAppraisal->status !== 'draft') {
            return false;
        }

        return true;
    }

    public function mount(): void
    {
        $user = Auth::user();

        $this->dueRecord = EmployeeAppraisal::query()
            ->where('employee_code', $user->employee->employee_code)
            ->where('status', 'Pending')
            ->get()
            ->filter(fn($r) => $r->isSubmissionWindowOpen())
            ->first();

        if ($this->dueRecord) {
            // Find the form again to determine state
            $existingAppraisal = Appraisal::query()
                ->where('employee_code', $user->employee->employee_code)
                ->where('appraisal_year', $this->dueRecord->appraisal_year)
                ->where('appraisal_month', $this->dueRecord->appraisal_month)
                ->first();

            if ($existingAppraisal) {
                // CASE: DRAFT EXISTS
                $this->actionUrl = AppraisalResource::getUrl('edit', ['record' => $existingAppraisal->id]);
                $this->buttonLabel = 'Resume Submission';
                
                // Update Badge for Draft
                $this->badgeLabel = 'DRAFT PENDING';
                $this->badgeColor = 'warning'; // Orange for attention
            } else {
                // CASE: FRESH START
                $this->actionUrl = AppraisalResource::getUrl('create');
                $this->buttonLabel = 'Fill Appraisal Form';
                
                // Badge for New
                $this->badgeLabel = 'OPEN';
                $this->badgeColor = 'success'; // Green for standard open
            }
        }
    }
}