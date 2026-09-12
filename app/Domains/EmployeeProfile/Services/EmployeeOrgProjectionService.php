<?php

namespace App\Domains\EmployeeProfile\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Models\EmployeeOrgReadModel;
use Illuminate\Support\Facades\DB;

class EmployeeOrgProjectionService
{
    public function rebuildForTenant(string $tenantId): int
    {
        return DB::transaction(function () use ($tenantId) {
            EmployeeOrgReadModel::where('tenant_id', $tenantId)->delete();

            $employees = Employee::where('tenant_id', $tenantId)
                ->with(['department', 'designation', 'branch', 'workLocation'])
                ->get();

            $count = 0;
            foreach ($employees as $emp) {
                $this->projectEmployee($emp);
                $count++;
            }

            return $count;
        });
    }

    public function projectEmployee(Employee $emp): EmployeeOrgReadModel
    {
        $hierarchy = $this->calculateHierarchy($emp);
        $spanOfControl = Employee::where('reporting_manager_id', $emp->id)
            ->where('employment_status', 'active')
            ->count();

        $searchableText = strtolower(implode(' ', array_filter([
            $emp->first_name,
            $emp->last_name,
            $emp->employee_code,
            $emp->employee_number,
            $emp->official_email,
            $emp->designation?->name,
            $emp->department?->name,
            $emp->branch?->name,
            $emp->workLocation?->name,
        ])));

        return EmployeeOrgReadModel::updateOrCreate(
            [
                'tenant_id' => $emp->tenant_id,
                'employee_id' => $emp->id,
            ],
            [
                'manager_id' => $emp->reporting_manager_id,
                'company_id' => $emp->company_id,
                'department_id' => $emp->department_id,
                'branch_id' => $emp->branch_id,
                'location_id' => $emp->work_location_id,
                'job_id' => null,
                'position_id' => $emp->current_position_id,
                'employee_number' => $emp->employee_code ?? $emp->employee_number,
                'full_name' => $emp->fullName(),
                'job_title' => $emp->designation?->name,
                'department_name' => $emp->department?->name,
                'branch_name' => $emp->branch?->name,
                'location_name' => $emp->workLocation?->name,
                'hierarchy_path' => $hierarchy['path'],
                'depth_level' => $hierarchy['depth'],
                'span_of_control' => $spanOfControl,
                'status' => $emp->employment_status,
                'photo_path' => $emp->photo_path,
                'searchable_text' => $searchableText,
            ]
        );
    }

    protected function calculateHierarchy(Employee $emp): array
    {
        $path = [$emp->id];
        $depth = 0;
        $current = $emp;
        $visited = [$emp->id => true];

        while ($current->reporting_manager_id && !isset($visited[$current->reporting_manager_id])) {
            $visited[$current->reporting_manager_id] = true;
            $manager = Employee::find($current->reporting_manager_id);
            if (!$manager) {
                break;
            }
            array_unshift($path, $manager->id);
            $depth++;
            $current = $manager;
            if ($depth > 20) {
                break; // guard against cycle
            }
        }

        return [
            'path' => '/' . implode('/', $path) . '/',
            'depth' => $depth,
        ];
    }
}
