<?php

namespace App\Domains\Offboarding\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Models\EmployeeAssignment;
use App\Domains\Offboarding\Enums\SeparationCategory;
use App\Domains\Offboarding\Enums\SeparationStatus;
use App\Domains\Offboarding\Models\SeparationAudit;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SeparationExecutionService
{
    public function __construct(protected ?SeparationValidationService $validationService = null)
    {
        $this->validationService = $validationService ?? new SeparationValidationService();
    }

    public function execute(SeparationRequest $request, ?User $executor = null): SeparationRequest
    {
        // Idempotency check
        if ($request->status === SeparationStatus::EXITED->value) {
            return $request;
        }

        // Validate clearance gating
        $this->validationService->validateReadyForExit($request);

        return DB::transaction(function () use ($request, $executor) {
            $employee = $request->employee;
            $category = $request->separationType->category ?? 'voluntary';

            // Determine core employee status
            $targetStatus = match ($category) {
                SeparationCategory::RETIREMENT->value => 'retired',
                SeparationCategory::INVOLUNTARY->value => 'terminated',
                default => 'separated',
            };

            $actualLwd = $request->approved_last_working_day ?? now()->toDateString();

            // 1. Update Core HR Employee master
            $employee->update([
                'employment_status' => $targetStatus,
                'termination_date' => $actualLwd,
            ]);

            // 2. Terminate active assignments in employee_assignments
            EmployeeAssignment::where('employee_id', $employee->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'terminated',
                    'effective_to' => $actualLwd,
                ]);

            // 3. Finalize separation request
            $request->update([
                'status' => SeparationStatus::EXITED->value,
                'actual_last_working_day' => $actualLwd,
                'executed_at' => now(),
                'executed_by' => $executor?->id ?? $request->approved_by,
            ]);

            SeparationAudit::create([
                'tenant_id' => $request->tenant_id,
                'separation_request_id' => $request->id,
                'actor_id' => $executor?->id,
                'event_name' => 'executed',
                'new_state' => ['status' => 'exited', 'employment_status' => $targetStatus, 'termination_date' => $actualLwd],
                'reason' => 'Separation executed: Core HR employee status updated to separated/terminated',
            ]);

            return $request;
        });
    }
}
