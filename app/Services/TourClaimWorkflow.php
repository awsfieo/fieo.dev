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
        $nextActor = $this->determineNextActor($claim, 'forward', $currentUser);

        // 2. If no one is next, it implies approval is needed (or end of chain)
        if (!$nextActor) {
            $this->approve($claim, $remarks);
            return;
        }

        $this->updateState($claim, 'reviewed', $nextActor, $remarks);
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

    public function reject(TourClaim $claim, string $reason): void
    {
        $claim->update([
            'current_state' => 'draft',
            'pending_with'  => null,
            'remarks'       => $reason,
            'file_history'  => $this->appendHistory($claim, 'Rejected', $reason, Auth::user()->employee),
        ]);
    }

    /**
     * THE LOGIC CORE
     */
    private function determineNextActor(TourClaim $claim, string $step, ?User $currentActor = null): ?Employee
    {
        $employee = $claim->employee;
        $dept     = $employee->department;
        $region   = $dept?->region ?? 'HO'; // 'WR', 'NR'...
        $deptType = $dept?->type ?? 'HO';   // 'CO', 'RO'...

        // --- START OF CHAIN ---
        if ($step === 'start') {
            // 1. Chapter Employees -> Chapter Head
            if ($deptType === 'CO') {
                return $this->findGenericRoleInScope('Chapter Head', officeId: $employee->office_id);
            }
            
            // 2. Regional Office Employees -> Accounts Executive {Region}
            if ($deptType === 'RO') {
                return $this->findGenericRoleInScope('Accounts Executive', region: $region);
            }

            // 3. Head Office / Departments -> HOD
            if ($deptType === 'HO' || $deptType === 'DO') {
                // If it's the Finance Dept itself, go straight to Executive
                if (str_contains(strtolower($dept->department ?? ''), 'finance')) {
                    return $this->findSpecificRole('Accounts Executive HO');
                }
                return $this->findGenericRoleInScope('HOD', deptId: $employee->department_id);
            }
        }

        // --- RELAY (FORWARDING) ---
        // We look at the Current Actor's Roles to decide the next step
        $roles = $currentActor?->getRoleNames()->toArray() ?? [];

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
            // Note: If this user is 'Accounts Executive HO', skip this block (handled below)
            if (!in_array('Accounts Executive HO', $roles)) {
                return $this->findGenericRoleInScope('Regional Head', region: $region);
            }
        }

        // 4. Regional Head -> Accounts Executive HO
        if (in_array('Regional Head', $roles)) {
            // Check Limits (Domestic <= 50k stops here)
            // Note: Adjust '50000' logic if you want strict checking against total expense
            $amount = ($claim->amount_reimburse_inr ?? 0) + ($claim->total_expenses_inr ?? 0);
            if ($claim->tour_type === 'domestic' && $amount <= 50000) {
                return null; // Stop chain (Approve)
            }
            return $this->findSpecificRole('Accounts Executive HO');
        }

        // 5. Accounts Executive HO -> HOD Finance
        if (in_array('Accounts Executive HO', $roles)) {
            return $this->findSpecificRole('HOD Finance');
        }

        // 6. HOD Finance -> DG & CEO
        if (in_array('HOD Finance', $roles)) {
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
        // In production, optimize this query to filter at SQL level
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

    private function updateState($claim, $state, $pendingWith, $remarks)
    {
        $claim->update([
            'current_state' => $state,
            'pending_with'  => $pendingWith?->id,
            'remarks'       => $remarks,
            'file_history'  => $this->appendHistory($claim, ucfirst($state), $remarks, Auth::user()->employee, $pendingWith?->id)
        ]);
    }

    private function appendHistory($claim, $action, $remarks, $actor, $toId = null): array
    {
        $history = $claim->file_history ?? [];
        $history[] = [
            'timestamp'  => now()->toDateTimeString(),
            'action'     => $action,
            'actor_name' => $actor?->name,
            'remarks'    => $remarks,
            'to_id'      => $toId
        ];
        return $history;
    }
}