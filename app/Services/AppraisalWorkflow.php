<?php

namespace App\Services;

use App\Models\Appraisal;
use App\Models\User;
use App\Models\Employee;
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
        if ($nextActor && $nextActor->user?->hasRole('DG & CEO')) {
            $nextStatus = 'final_review_pending';
        }

        // 3. Update Status & Pending With
        $appraisal->update([
            'status'       => $nextStatus,
            'pending_with' => $nextActor?->employee_code,
            'file_history' => $this->appendHistory(
                $appraisal,
                'Submitted',
                'Self-Appraisal submitted. Forwarded to ' . ($nextActor->designation->designation ?? 'Reporting Officer') . '.',
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
            : 'final_review_pending';

        $appraisal->update([
            'status'       => $nextStatus,
            'pending_with' => $nextActor?->employee_code,
            'file_history' => $this->appendHistory(
                $appraisal, 'Assessed', 'Common Evaluation completed.', $currentUser->employee, $nextActor?->employee_code
            ),
        ]);
    }

    public function reviewRegional(Appraisal $appraisal): void
    {
        $dg = $this->findRole('DG & CEO');

        $appraisal->update([
            'status'       => 'final_review_pending',
            'pending_with' => $dg?->employee_code,
            'file_history' => $this->appendHistory(
                $appraisal, 'Regional Review', 'Regional assessment completed.', Auth::user()->employee, $dg?->employee_code
            ),
        ]);
    }

    public function finalize(Appraisal $appraisal): void
    {
        $appraisal->update([
            'status'       => 'closed',
            'pending_with' => null,
            'file_history' => $this->appendHistory(
                $appraisal, 'Finalized', 'Annual Increment decided by DG & CEO.', Auth::user()->employee
            ),
        ]);
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
        $history = $appraisal->file_history ?? [];
        $history[] = [
            'timestamp'  => now()->toDateTimeString(),
            'action'     => $action,
            'actor_name' => $actor?->name,
            'actor_code' => $actor?->employee_code,
            'remarks'    => $remarks,
            'to_code'    => $toCode
        ];
        return $history;
    }
}