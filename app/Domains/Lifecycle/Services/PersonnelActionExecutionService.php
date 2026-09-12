<?php

namespace App\Domains\Lifecycle\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Models\EmployeeAssignment;
use App\Domains\Lifecycle\Enums\PersonnelActionStatus;
use App\Domains\Lifecycle\Models\PersonnelActionAudit;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PersonnelActionExecutionService
{
    public function execute(PersonnelActionRequest $request, ?User $executor = null): PersonnelActionRequest
    {
        // Idempotency check
        if ($request->status === PersonnelActionStatus::EXECUTED->value) {
            return $request;
        }

        if (!in_array($request->status, [PersonnelActionStatus::APPROVED->value, PersonnelActionStatus::SCHEDULED->value])) {
            throw ValidationException::withMessages(['status' => 'Only approved or scheduled personnel actions can be executed.']);
        }

        return DB::transaction(function () use ($request, $executor) {
            $employee = $request->employee;
            $changes = $request->changes;

            $employeeUpdates = [];
            foreach ($changes as $change) {
                $fieldName = $change->field_name;

                // Map allowable employee attributes
                if (in_array($fieldName, [
                    'department_id',
                    'designation_id',
                    'reporting_manager_id',
                    'job_grade_id',
                    'employment_status',
                    'branch_id',
                    'work_location_id',
                    'employment_type_id',
                ])) {
                    $employeeUpdates[$fieldName] = $change->new_value;
                }
            }

            if (!empty($employeeUpdates)) {
                $employee->update($employeeUpdates);
            }

            // Create effective-dated employee assignment record if structural attributes changed
            if (isset($employeeUpdates['department_id']) || isset($employeeUpdates['designation_id']) || isset($employeeUpdates['reporting_manager_id'])) {
                $employmentId = $employee->current_employment_id
                    ?? \App\Domains\Employee\Models\Employment::where('employee_id', $employee->id)->value('id');

                if (!$employmentId) {
                    $empRec = \App\Domains\Employee\Models\Employment::create([
                        'tenant_id' => $employee->tenant_id,
                        'employee_id' => $employee->id,
                        'company_id' => $employee->company_id,
                        'employment_number' => 'EMP-P-' . strtoupper(bin2hex(random_bytes(4))),
                        'start_date' => $employee->joining_date ?? now()->toDateString(),
                        'effective_from' => $employee->joining_date ?? now()->toDateString(),
                        'status' => 'active',
                    ]);
                    $employmentId = $empRec->id;
                    $employee->update(['current_employment_id' => $employmentId]);
                }

                EmployeeAssignment::create([
                    'tenant_id' => $request->tenant_id,
                    'employee_id' => $employee->id,
                    'employment_id' => $employmentId,
                    'company_id' => $employee->company_id,
                    'department_id' => $employee->department_id,
                    'position_id' => $employeeUpdates['position_id'] ?? $employee->current_position_id,
                    'manager_employee_id' => $employee->reporting_manager_id,
                    'is_primary' => true,
                    'effective_from' => $request->effective_date->toDateString(),
                    'reason' => $request->reason ?? $request->actionType->name,
                    'status' => 'active',
                ]);
            }

            $request->update([
                'status' => PersonnelActionStatus::EXECUTED->value,
                'executed_at' => now(),
                'executed_by' => $executor?->id ?? $request->approved_by,
            ]);

            PersonnelActionAudit::create([
                'tenant_id' => $request->tenant_id,
                'personnel_action_request_id' => $request->id,
                'actor_id' => $executor?->id,
                'action_event' => 'executed',
                'new_values' => $employeeUpdates,
                'reason' => 'Executed changes applied to Core HR master records',
            ]);

            return $request;
        });
    }
}
