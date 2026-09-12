<?php

namespace App\Domains\Attendance\Services\Scheduling;

use App\Domains\Attendance\Models\HcmShiftSwapRequest;
use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ShiftSwapService
{
    public function __construct(
        protected ScheduleEligibilityService $eligibilityService
    ) {}

    /**
     * Request a shift swap between two assignments/employees.
     */
    public function requestSwap(
        RosterAssignment $requestingAssignment,
        RosterAssignment $targetAssignment,
        Employee $requestingEmployee,
        Employee $targetEmployee,
        string $reason
    ): HcmShiftSwapRequest {
        if ($requestingAssignment->tenant_id !== $targetAssignment->tenant_id) {
            throw ValidationException::withMessages([
                'swap' => ['Cannot swap assignments across different tenants.'],
            ]);
        }

        // Validate requesting employee on target shift
        $eligibilityA = $this->eligibilityService->checkEligibility(
            employee: $requestingEmployee,
            date: $targetAssignment->roster_date->toDateString(),
            shift: $targetAssignment->shift,
            currentAssignmentId: $requestingAssignment->id
        );

        // Validate target employee on requesting shift
        $eligibilityB = $this->eligibilityService->checkEligibility(
            employee: $targetEmployee,
            date: $requestingAssignment->roster_date->toDateString(),
            shift: $requestingAssignment->shift,
            currentAssignmentId: $targetAssignment->id
        );

        $hasHardViolations = ! $eligibilityA['is_eligible'] || ! $eligibilityB['is_eligible'];
        if ($hasHardViolations) {
            $errors = array_merge($eligibilityA['hard_violations'], $eligibilityB['hard_violations']);
            throw ValidationException::withMessages([
                'swap_eligibility' => $errors,
            ]);
        }

        return HcmShiftSwapRequest::query()->create([
            'tenant_id' => $requestingAssignment->tenant_id,
            'requesting_assignment_id' => $requestingAssignment->id,
            'target_assignment_id' => $targetAssignment->id,
            'requesting_employee_id' => $requestingEmployee->id,
            'target_employee_id' => $targetEmployee->id,
            'status' => 'pending_peer',
            'reason' => $reason,
            'validation_payload' => [
                'requesting_eligibility' => $eligibilityA,
                'target_eligibility' => $eligibilityB,
            ],
        ]);
    }

    /**
     * Peer employee responds to the swap request.
     */
    public function peerRespond(HcmShiftSwapRequest $swapRequest, bool $accept): HcmShiftSwapRequest
    {
        if ($swapRequest->status !== 'pending_peer') {
            throw ValidationException::withMessages([
                'swap' => ['Swap request is not pending peer response.'],
            ]);
        }

        $swapRequest->update([
            'status' => $accept ? 'pending_manager' : 'rejected',
            'peer_response_at' => now(),
        ]);

        return $swapRequest->fresh();
    }

    /**
     * Manager approves or rejects the swap request.
     */
    public function managerApprove(
        HcmShiftSwapRequest $swapRequest,
        User $manager,
        bool $approve
    ): HcmShiftSwapRequest {
        if ($swapRequest->status !== 'pending_manager') {
            throw ValidationException::withMessages([
                'swap' => ['Swap request is not pending manager approval.'],
            ]);
        }

        if (! $approve) {
            $swapRequest->update([
                'status' => 'rejected',
                'approved_by' => $manager->id,
                'approved_at' => now(),
            ]);
            return $swapRequest->fresh();
        }

        // Execute swap
        $assignmentA = $swapRequest->requestingAssignment;
        $assignmentB = $swapRequest->targetAssignment;

        if ($assignmentA && $assignmentB) {
            $shiftA = $assignmentA->shift_definition_id;
            $shiftB = $assignmentB->shift_definition_id;

            $assignmentA->update([
                'shift_definition_id' => $shiftB,
                'assignment_status' => 'swapped',
                'replacement_employee_id' => $assignmentB->employee_id,
                'updated_by' => $manager->id,
            ]);

            $assignmentB->update([
                'shift_definition_id' => $shiftA,
                'assignment_status' => 'swapped',
                'replacement_employee_id' => $assignmentA->employee_id,
                'updated_by' => $manager->id,
            ]);
        }

        $swapRequest->update([
            'status' => 'approved',
            'approved_by' => $manager->id,
            'approved_at' => now(),
        ]);

        return $swapRequest->fresh();
    }
}
