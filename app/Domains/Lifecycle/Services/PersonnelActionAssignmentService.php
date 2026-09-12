<?php

namespace App\Domains\Lifecycle\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Models\PersonnelTemporaryAssignment;
use Carbon\Carbon;

class PersonnelActionAssignmentService
{
    public function createAssignment(Employee $employee, array $data, ?PersonnelActionRequest $request = null): PersonnelTemporaryAssignment
    {
        return PersonnelTemporaryAssignment::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'personnel_action_request_id' => $request?->id,
            'assignment_type' => $data['assignment_type'],
            'home_department_id' => $employee->department_id,
            'temporary_department_id' => $data['temporary_department_id'] ?? null,
            'home_position_id' => $employee->current_position_id,
            'temporary_position_id' => $data['temporary_position_id'] ?? null,
            'home_manager_id' => $employee->reporting_manager_id,
            'temporary_manager_id' => $data['temporary_manager_id'] ?? null,
            'start_date' => Carbon::parse($data['start_date'])->toDateString(),
            'end_date' => Carbon::parse($data['end_date'])->toDateString(),
            'status' => 'active',
            'reason' => $data['reason'] ?? null,
        ]);
    }

    public function completeAndRevertAssignment(PersonnelTemporaryAssignment $assignment): PersonnelTemporaryAssignment
    {
        $employee = $assignment->employee;

        // Restore home department and manager if specified
        $restore = [];
        if ($assignment->home_department_id) {
            $restore['department_id'] = $assignment->home_department_id;
        }
        if ($assignment->home_manager_id) {
            $restore['reporting_manager_id'] = $assignment->home_manager_id;
        }

        if (!empty($restore)) {
            $employee->update($restore);
        }

        $assignment->update(['status' => 'reverted']);

        return $assignment;
    }
}
