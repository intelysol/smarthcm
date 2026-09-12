<?php

namespace App\Domains\Offboarding\Services;

use App\Domains\Employee\Models\EmployeeAssignment;
use App\Domains\Offboarding\Enums\SeparationStatus;
use App\Domains\Offboarding\Models\SeparationAudit;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationReversal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SeparationReversalService
{
    public function reverseSeparation(SeparationRequest $request, User $user, string $reason, string $actionType = 'reversal'): SeparationReversal
    {
        if ($request->status !== SeparationStatus::EXITED->value) {
            throw ValidationException::withMessages([
                'status' => 'Only finalized/exited separations can be reversed or reinstated.',
            ]);
        }

        return DB::transaction(function () use ($request, $user, $reason, $actionType) {
            $employee = $request->employee;

            // 1. Revert Core HR Employee status to active
            $employee->update([
                'employment_status' => 'active',
                'termination_date' => null,
            ]);

            // 2. Reactivate latest assignment
            $lastAssignment = EmployeeAssignment::where('employee_id', $employee->id)->latest()->first();
            if ($lastAssignment) {
                $lastAssignment->update([
                    'status' => 'active',
                    'effective_to' => null,
                ]);
            }

            // 3. Mark request as REVERSED
            $request->update(['status' => SeparationStatus::REVERSED->value]);

            // 4. Create Reversal Record
            $reversal = SeparationReversal::create([
                'tenant_id' => $request->tenant_id,
                'separation_request_id' => $request->id,
                'action_type' => $actionType,
                'reason' => $reason,
                'requested_by' => $user->id,
                'approved_by' => $user->id,
                'executed_at' => now(),
            ]);

            SeparationAudit::create([
                'tenant_id' => $request->tenant_id,
                'separation_request_id' => $request->id,
                'actor_id' => $user->id,
                'event_name' => 'reversed',
                'new_state' => ['status' => 'reversed', 'employment_status' => 'active'],
                'reason' => "Separation {$actionType}: {$reason}",
            ]);

            return $reversal;
        });
    }
}
