<?php

namespace App\Services;

use App\Models\TourClaim;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class TourClaimWorkflow
{
    /**
     * Submit a claim to start the chain.
     */
    public function submit(TourClaim $claim): void
    {
        // 1. Find the first reviewer based on Applicant's context
        $nextActor = $this->determineNextActor($claim, 'start');

        // 2. Update state
        $this->updateState($claim, 'submitted', $nextActor, 'Claim submitted for review.');
    }

    /**
     * Forward to the next person (Relay).
     */
    public function forward(TourClaim $claim, string $remarks): void
    {
        $currentUser = Auth::user();
        
        // 1. Find next actor based on Current User's Role
        $nextActor = $this->determineNextActor($claim, 'Forward', $currentUser);

        // 2. If no one is next, it implies approval is needed (or end of chain)
        if (!$nextActor) {
            $this->approve($claim, $remarks);
            return;
        }

        $this->updateState($claim, 'Reviewed', $nextActor, $remarks);
    }

    public function approve(TourClaim $claim, string $remarks): void
    {
        $claim->update([
            'current_state' => 'approved',
            'pending_with'  => null,
            'closed_at'     => now(),
            'remarks'       => $remarks,
            'file_history'  => $this->appendHistory($claim, 'Approved', 'Final Approval. ' . $remarks, Auth::user()->employee),
        ]);
    }

    public function query(TourClaim $claim, string $reason): void
    {
        $claim->update([
            'current_state' => 'query',
            'pending_with'  => null,
            'remarks'       => $reason,
            'file_history'  => $this->appendHistory($claim, 'Query', $reason, Auth::user()->employee),
        ]);
    }

    /**
     * THE LOGIC CORE
     * CHANGED: Made public so the View can "peek" ahead.
     */
    public function determineNextActor(TourClaim $claim, string $step, ?User $currentActor = null): ?Employee
    {
        $employee = $claim->employee;
        $dept     = $employee->department;
        $region   = $dept?->region ?? 'HO'; 
        $deptType = $dept?->type ?? 'HO';   

        // --- START OF CHAIN ---
        if ($step === 'start') {
            if ($deptType === 'CO') {
                return $this->findGenericRoleInScope('Chapter Head', officeId: $employee->office_id);
            }
            if ($deptType === 'RO') {
                return $this->findGenericRoleInScope('Accounts Executive', region: $region);
            }
            if ($deptType === 'HO' || $deptType === 'DO') {
                if (str_contains(strtolower($dept->department ?? ''), 'finance')) {
                    return $this->findSpecificRole('Accounts Executive HO');
                }
                return $this->findGenericRoleInScope('HOD', deptId: $employee->department_id);
            }
        }

        // --- RELAY (FORWARDING) ---
        $roles = $currentActor?->getRoleNames()->toArray() ?? [];
        
        // Common amount calculation for limits
        $amount = ($claim->amount_reimburse_inr ?? 0) + ($claim->total_expenses_inr ?? 0);

        // 1. Chapter Head -> Accounts Executive {Region}
        if (in_array('Chapter Head', $roles)) {
            return $this->findGenericRoleInScope('Accounts Executive', region: $region);
        }

        // 2. HOD -> Accounts Executive HO
        if (in_array('HOD', $roles)) {
            return $this->findSpecificRole('Accounts Executive HO');
        }

        // 3. Accounts Executive (Region) -> Regional Head {Region}
        if (in_array('Accounts Executive', $roles)) {
            if (!in_array('Accounts Executive HO', $roles)) {
                return $this->findGenericRoleInScope('Regional Head', region: $region);
            }
        }

        // 4. Regional Head -> Accounts Executive HO (With 50k Limit)
        if (in_array('Regional Head', $roles)) {
            // Logic: If Domestic & <= 50k, Stop (Approve). Else, Forward.
            if ($claim->tour_type === 'domestic' && $amount <= 50000) {
                return null; 
            }
            return $this->findSpecificRole('Accounts Executive HO');
        }

        // 5. Accounts Executive HO -> HOD Finance
        if (in_array('Accounts Executive HO', $roles)) {
            return $this->findSpecificRole('HOD Finance');
        }

        // 6. HOD Finance -> DG & CEO (With 10k Limit) -- NEW RULE
        if (in_array('HOD Finance', $roles)) {
            // Logic: If Domestic & <= 10k, Stop (Approve). Else, Forward.
            if ($claim->tour_type === 'domestic' && $amount <= 10000) {
                return null; 
            }
            return $this->findSpecificRole('DG & CEO');
        }

        return null;
    }

    private function findSpecificRole(string $role): ?Employee
    {
        return User::role($role)->first()?->employee;
    }

    private function findGenericRoleInScope(string $role, ?int $officeId=null, ?int $deptId=null, ?string $region=null): ?Employee
    {
        $users = User::role($role)->get();

        foreach ($users as $user) {
            $emp = $user->employee;
            if (!$emp) continue;

            if ($officeId && $emp->office_id === $officeId) return $emp;
            if ($deptId && $emp->department_id === $deptId) return $emp;
            if ($region && ($emp->department->region ?? '') === $region) return $emp;
        }
        return null;
    }

    private function updateState($claim, $state, $pendingWithEmployee, $remarks)
    {
        $claim->update([
            'current_state' => $state,
            'pending_with'  => $pendingWithEmployee?->employee_code, 
            'remarks'       => $remarks,
            'file_history'  => $this->appendHistory(
                $claim, 
                ucfirst($state), 
                $remarks, 
                Auth::user()->employee, 
                $pendingWithEmployee?->employee_code
            )
        ]);
    }

    private function appendHistory($claim, $action, $remarks, $actor, $toCode = null): array
    {
        $history = $claim->file_history ?? [];
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