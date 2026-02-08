<?php

namespace App\Services;

use App\Models\Appraisal;
use App\Models\User;
use App\Models\Employee;
use App\Models\EmployeeAppraisal;
use Illuminate\Support\Facades\Auth;

class AppraisalWorkflow
{
    /**
     * Handle the submission by the Employee.
     */
    public function submit(Appraisal $appraisal): void
    {
        // 1. Determine the Reporting Officer
        $nextActor = $this->determineReportingOfficer($appraisal);

        // 2. Determine Status based on Destination
        // Default: 'submitted' (Goes to Common Evaluation / Part B)
        $nextStatus = 'submitted';

        // FIX: If routing directly to DG & CEO, SKIP intermediate steps.
        // Jump straight to Final Review (Part D).
        // if ($nextActor && $nextActor->user?->hasRole('DG & CEO')) {
        //     $nextStatus = 'final_review_pending';
        // }

        // 3. Update Status & Pending With
        $appraisal->update([
            'status'       => $nextStatus,
            'pending_with' => $nextActor?->employee_code,
            'file_history' => $this->appendHistory(
                $appraisal,
                'Submitted',
                'Appraisal Form submitted. Forwarded to ' . ($nextActor->name ?? 'Supervising Officer') . ', ' . ($nextActor->designation->designation ?? ''),
                Auth::user()->employee,
                $nextActor?->employee_code
            ),
        ]);
    }

    // ... (assess, reviewRegional, finalize methods remain same) ...
    public function assess(Appraisal $appraisal): void
    {
        $currentUser = Auth::user();
        $nextActor = $this->determineReviewingOfficer($appraisal, $currentUser);

        $nextStatus = $nextActor && $nextActor->user?->hasRole('Regional Head')
            ? 'regional_head_review_pending'
            : 'final_assessment_pending';

        $appraisal->update([
            'status'       => $nextStatus,
            'pending_with' => $nextActor?->employee_code,
            'file_history' => $this->appendHistory(
                $appraisal,
                'Supervisor Evaluated',
                'Evaluation completed by Supervising Officer.',
                $currentUser->employee,
                $nextActor?->employee_code
            ),
        ]);
    }

    public function reviewRegional(Appraisal $appraisal): void
    {
        $dg = $this->findRole('DG & CEO');

        $appraisal->update([
            'status'       => 'final_assessment_pending',
            'pending_with' => $dg?->employee_code,
            'file_history' => $this->appendHistory(
                $appraisal,
                'Regional Head Reviewed',
                'Regional Head review completed.',
                Auth::user()->employee,
                $dg?->employee_code
            ),
        ]);
    }

    public function finalize(Appraisal $appraisal): void
    {
        $appraisal->update([
            'status'       => 'completed',
            'pending_with' => null,
            'file_history' => $this->appendHistory(
                $appraisal,
                'Appraisal Completed',
                'Final Assessment done by DG & CEO',
                Auth::user()->employee
            ),
        ]);

        // 2. Sync Result to EmployeeAppraisal (The "Increment Order" Table)
        $pctString = $appraisal->final_increment; // e.g. "5%" or "0%"

        // Extract number from string (e.g. "5%" -> 5.0)
        $pctValue = (float) filter_var($pctString, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        // Determine if increment is granted (> 0)
        $isGranted = $pctValue > 0;

        // LOGIC CHANGE: If 0% increment, set status to 'Hold', otherwise 'Processed'
        $hrStatus = $isGranted ? 'Processed' : 'Hold';

        // --- UPDATED LOGIC START ---
        // Fetch the model instance first so the 'encrypted' cast works on save
        $empAppraisal = EmployeeAppraisal::query()
            ->where('employee_code', $appraisal->employee_code)
            ->where('appraisal_year', $appraisal->appraisal_year)
            ->where('appraisal_month', $appraisal->appraisal_month)
            ->first();

        if ($empAppraisal) {
            $empAppraisal->update([
                'appraisal_id'         => $appraisal->id,
                'increment_percentage' => $pctString, // Will now be encrypted automatically
                'increment_granted'    => $isGranted,
                'status'               => $hrStatus,
                'updated_at'           => now(),
            ]);
        }
    }

    // --- HELPER LOGIC ---

    public function determineReportingOfficer(Appraisal $appraisal): ?Employee
    {
        $employee = $appraisal->employee;
        $user = $employee->user;

        // --- SPECIAL LOGIC FOR HEADS ---

        // Case A: Chapter Head Submits -> Goes to Regional Head
        if ($user && $user->hasRole('Chapter Head')) {
            // Find Regional Head for this Chapter's region
            $region = $employee->office?->region ?? $employee->department?->region;
            return $this->findRoleInScope('Regional Head', region: $region);
        }

        // Case B: Regional Head OR HOD Submits -> Goes to DG & CEO directly
        if ($user && ($user->hasRole('Regional Head') || $user->hasRole('HOD'))) {
            return $this->findRole('DG & CEO');
        }

        // --- STANDARD LOGIC FOR STAFF ---

        $officeType = $employee->office?->type;
        $region = $employee->department?->region ?? $employee->office?->region;

        // 1. Chapter Office (CO) -> Goes to Chapter Head
        if ($officeType === 'CO' || str_contains(strtolower($employee->office?->office ?? ''), 'chapter')) {
            return $this->findRoleInScope('Chapter Head', officeId: $employee->office_id);
        }

        // 2. Regional Office (RO) -> Goes to Regional Head
        if ($officeType === 'RO' || str_contains(strtolower($employee->office?->office ?? ''), 'region')) {
            return $this->findRoleInScope('Regional Head', region: $region);
        }

        // 3. Head Office (HO) -> Goes to HOD
        return $this->findRoleInScope('HOD', deptId: $employee->department_id);
    }

    public function determineReviewingOfficer(Appraisal $appraisal, User $currentActor): ?Employee
    {
        if ($currentActor->hasRole('Chapter Head')) {
            $region = $appraisal->employee->office?->region ?? $appraisal->employee->department?->region;
            return $this->findRoleInScope('Regional Head', region: $region);
        }
        return $this->findRole('DG & CEO');
    }

    private function findRole(string $role): ?Employee
    {
        return User::role($role)->first()?->employee;
    }

    private function findRoleInScope(string $role, ?int $officeId = null, ?int $deptId = null, ?string $region = null): ?Employee
    {
        $users = User::role($role)->get();

        foreach ($users as $user) {
            $emp = $user->employee;
            if (!$emp) continue;

            if ($region && ($emp->department?->region === $region || $emp->office?->region === $region)) return $emp;
            if ($deptId && $emp->department_id === $deptId) return $emp;
            if ($officeId && $emp->office_id === $officeId) return $emp;
        }

        return null;
    }

    private function appendHistory($appraisal, $action, $remarks, $actor, $toCode = null): array
    {
        // 1. Resolve the Name from the DB column 'pending_with' (which is the code)
        $pendingWithName = null;
        if ($toCode) {
            $pendingWithName = Employee::where('employee_code', $toCode)->value('name');
        }

        $history = $appraisal->file_history ?? [];
        $history[] = [
            'timestamp'    => now()->toDateTimeString(),
            'action'       => $action,
            'actor_name'   => $actor?->name,
            'actor_code'   => $actor?->employee_code,
            'remarks'      => $remarks,
            'pending_with' => $pendingWithName, // <--- Saving the Name, not the Code
        ];

        return $history;
    }
}
