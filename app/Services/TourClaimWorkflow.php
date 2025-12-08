<?php

namespace App\Services;

use App\Models\TourClaim;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class TourClaimWorkflow
{
    public function submit(TourClaim $claim): void
    {
        $nextActor = $this->determineNextActor($claim, 'start');
        $this->updateState($claim, 'submitted', $nextActor, 'Claim submitted for review.');
    }

    public function forward(TourClaim $claim, string $remarks): void
    {
        $currentUser = Auth::user();
        $nextActor = $this->determineNextActor($claim, 'Forward', $currentUser);

        if (!$nextActor) {
            $this->approve($claim, $remarks);
            return;
        }

        $this->updateState($claim, 'Reviewed', $nextActor, $remarks);
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
     * Employee replies to a query and resubmits.
     */
    public function replyToQuery(TourClaim $claim, string $reply): void
    {
        // 1. Restart the chain (usually goes to Accounts Executive)
        $nextActor = $this->determineNextActor($claim, 'start');

        // 2. Update state to 'submitted'
        // We prepend "Query Reply:" so the reviewer immediately knows context
        $this->updateState($claim, 'submitted', $nextActor, 'Query Reply: ' . $reply);
    }

    public function determineNextActor(TourClaim $claim, string $step, ?User $currentActor = null): ?Employee
    {
        $employee = $claim->employee;
        $dept     = $employee->department;
        $region   = $dept?->region ?? 'HO';
        $deptType = $dept?->type ?? 'HO';

        // --- START OF CHAIN ---
        if ($step === 'start') {
            // REVERTED: Standard logic applies (Removed the NR bypass check)
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
        $amount = ($claim->amount_reimburse_inr ?? 0) + ($claim->total_expenses_inr ?? 0);

        // 1. Chapter Head -> Accounts Executive {Region}
        if (in_array('Chapter Head', $roles)) {
            // REVERTED: Standard logic applies (Removed the NR bypass check)
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

        // 4. Regional Head -> Accounts Executive HO (With Checks)
        if (in_array('Regional Head', $roles)) {
            // [NEW LOGIC]: If Region is NR, ALWAYS forward to Acc Exec HO (No approval power)
            if ($region === 'NR') {
                return $this->findSpecificRole('Accounts Executive HO');
            }

            // Standard Logic: If Domestic & <= 50k, Stop (Approve).
            if ($claim->tour_type === 'domestic' && $amount <= 50000) {
                return null;
            }
            return $this->findSpecificRole('Accounts Executive HO');
        }

        // 5. Accounts Executive HO -> HOD Finance
        if (in_array('Accounts Executive HO', $roles)) {
            return $this->findSpecificRole('HOD Finance');
        }

        // 6. HOD Finance -> DG & CEO (With 10k Limit)
        if (in_array('HOD Finance', $roles)) {
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

    private function findGenericRoleInScope(string $role, ?int $officeId = null, ?int $deptId = null, ?string $region = null): ?Employee
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

    public function approve(TourClaim $claim, string $remarks): void
    {
        // 1. Generate Sanction Order Number (Simple Logic)
        // Format: SO/FY-YEAR/ID (e.g., SO/24-25/105)
        $fy = date('y') . '-' . (date('y') + 1);
        $sanctionNo = 'SO/' . $fy . '/' . str_pad($claim->id, 4, '0', STR_PAD_LEFT);

        // 2. Find the Accounts Person responsible for payment
        // Usually, this goes back to the Accounts Executive who started it, or HO Accounts
        $accountsRole = $claim->employee->office->type === 'HO' ? 'Accounts Executive HO' : 'Accounts Executive';
        $accountsPerson = $this->findGenericRoleInScope($accountsRole, region: $claim->employee->department->region);

        // Fallback: If no regional accounts, send to HO
        if (!$accountsPerson) {
            $accountsPerson = $this->findSpecificRole('Accounts Executive HO');
        }

        // 3. Update State -> 'approved' but PENDING with Accounts
        $claim->update([
            'current_state'     => 'approved', // Status is approved
            'sanction_order_no' => $sanctionNo,
            'pending_with'      => $accountsPerson?->employee_code, // Assigned to Accounts for payment
            'remarks'           => $remarks,
            'file_history'      => $this->appendHistory(
                $claim, 
                'Approved', 
                "Sanction Order $sanctionNo Issued. Forwarded to Accounts for Settlement. Note: $remarks", 
                Auth::user()->employee,
                $accountsPerson?->employee_code
            ),
        ]);
    }

    public function settle(TourClaim $claim, array $data): void
    {
        $claim->update([
            'current_state'     => 'settled',
            'pending_with'      => null, // Workflow Ends Here
            'closed_at'         => now(),
            
            // Save Payment Details
            'settlement_date'   => $data['date'],
            'settlement_amount' => $data['amount'],
            'settlement_utr'    => $data['utr'],
            'settlement_remarks'=> $data['remarks'] ?? null,

            'file_history'      => $this->appendHistory(
                $claim, 
                'Settled', 
                "Payment Processed. UTR: {$data['utr']}, Amt: {$data['amount']}", 
                Auth::user()->employee
            ),
        ]);
    }
}
