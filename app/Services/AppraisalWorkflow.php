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
        // 1. Determine the Reporting Officer (Chapter Head / Regional Head / HOD)
        $nextActor = $this->determineReportingOfficer($appraisal);

        // 2. Update Status & Pending With
        $appraisal->update([
            'status'       => 'submitted', // Moves to "Common Evaluation" stage
            'pending_with' => $nextActor?->employee_code,
            'file_history' => $this->appendHistory(
                $appraisal,
                'Submitted',
                'Self-Appraisal submitted. Forwarded to Reporting Officer.',
                Auth::user()->employee,
                $nextActor?->employee_code
            ),
        ]);
    }

    /**
     * Handle the assessment by Reporting Officer (HOD / Chapter Head).
     */
    public function assess(Appraisal $appraisal): void
    {
        $currentUser = Auth::user();
        
        // Determine if this needs to go to Regional Head or straight to DG
        $nextActor = $this->determineReviewingOfficer($appraisal, $currentUser);
        
        // If next is Regional Head, status is 'regional_head_review_pending'
        // If next is DG, status is 'final_review_pending'
        $nextStatus = $nextActor && $nextActor->user?->hasRole('Regional Head') 
            ? 'regional_head_review_pending' 
            : 'final_review_pending';

        $appraisal->update([
            'status'       => $nextStatus,
            'pending_with' => $nextActor?->employee_code,
            'file_history' => $this->appendHistory(
                $appraisal,
                'Assessed',
                'Common Evaluation completed.',
                $currentUser->employee,
                $nextActor?->employee_code
            ),
        ]);
    }

    /**
     * Regional Head reviews Chapter Head's assessment.
     */
    public function reviewRegional(Appraisal $appraisal): void
    {
        $dg = $this->findRole('DG & CEO');

        $appraisal->update([
            'status'       => 'final_review_pending',
            'pending_with' => $dg?->employee_code,
            'file_history' => $this->appendHistory(
                $appraisal,
                'Regional Review',
                'Regional assessment completed. Forwarded to DG & CEO.',
                Auth::user()->employee,
                $dg?->employee_code
            ),
        ]);
    }

    /**
     * Final DG Assessment.
     */
    public function finalize(Appraisal $appraisal): void
    {
        $appraisal->update([
            'status'       => 'closed',
            'pending_with' => null, // Process End
            'file_history' => $this->appendHistory(
                $appraisal,
                'Finalized',
                'Annual Increment decided by DG & CEO.',
                Auth::user()->employee
            ),
        ]);
    }

    // --- HELPER LOGIC ---

    public function determineReportingOfficer(Appraisal $appraisal): ?Employee
    {
        $employee = $appraisal->employee;
        $department = $employee->department;

        // CRITICAL FIX: Determine workflow based on Department Type/Region, NOT Office ID.
        // This mirrors the logic in TourClaimWorkflow.
        $region = $department?->region; // e.g., 'WR', 'NR', 'SR'
        $deptType = $department?->type; // e.g., 'RO', 'CO', 'HO'

        // 1. If Employee is in a CHAPTER OFFICE (CO) -> Goes to Chapter Head
        if ($deptType === 'CO' || str_contains(strtolower($employee->office?->office ?? ''), 'chapter')) {
            // Note: Chapter Heads are still typically found by Office ID, 
            // but if you want strict department logic, ensure Chapter Heads have a distinct Department Role.
            // For now, we keep Office ID for Chapter Heads ONLY as per TourClaimWorkflow pattern.
            return $this->findRoleInScope('Chapter Head', officeId: $employee->office_id);
        }

        // 2. If Employee is in REGIONAL OFFICE (RO) -> Goes to Regional Head
        // FIX: We now use the REGION string to find the Regional Head.
        if ($deptType === 'RO' || str_contains(strtolower($employee->office?->office ?? ''), 'regional')) {
            return $this->findRoleInScope('Regional Head', region: $region);
        }

        // 3. If Employee is in HEAD OFFICE (HO) -> Goes to HOD
        return $this->findRoleInScope('HOD', deptId: $employee->department_id);
    }

    public function determineReviewingOfficer(Appraisal $appraisal, User $currentActor): ?Employee
    {
        // If the current actor is a Chapter Head, it MUST go to Regional Head
        if ($currentActor->hasRole('Chapter Head')) {
            // Find Regional Head for this Chapter's region
            $region = $appraisal->employee->department?->region ?? 'HO'; 
            return $this->findRoleInScope('Regional Head', region: $region);
        }

        // Otherwise (HOD or Regional Head acting as RO), it goes to DG
        return $this->findRole('DG & CEO');
    }

    private function findRole(string $role): ?Employee
    {
        return User::role($role)->first()?->employee;
    }

    private function findRoleInScope(string $role, ?int $officeId = null, ?int $deptId = null, ?string $region = null): ?Employee
    {
        // Retrieve all users with the required Role
        $users = User::role($role)->get();

        foreach ($users as $user) {
            $emp = $user->employee;
            if (!$emp) continue;

            // Priority 1: Match by Region (Critical for Regional Heads)
            // Checks if the Regional Head's department region matches the target region (e.g., 'WR')
            if ($region && ($emp->department?->region === $region)) {
                return $emp;
            }

            // Priority 2: Match by Department ID (For HODs)
            if ($deptId && $emp->department_id === $deptId) {
                return $emp;
            }

            // Priority 3: Match by Office ID (For Chapter Heads)
            if ($officeId && $emp->office_id === $officeId) {
                return $emp;
            }
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