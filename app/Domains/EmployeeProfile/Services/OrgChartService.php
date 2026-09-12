<?php

namespace App\Domains\EmployeeProfile\Services;

use App\Domains\Employee\Models\Employee;

class OrgChartService
{
    public function getRootNodes(string $tenantId): array
    {
        // Top-level leaders (no reporting manager)
        $roots = Employee::where('tenant_id', $tenantId)
            ->whereNull('reporting_manager_id')
            ->where('employment_status', 'active')
            ->with(['department', 'designation'])
            ->get();

        // If all employees have a manager cycle or no null manager, take the earliest joined active employee
        if ($roots->isEmpty()) {
            $roots = Employee::where('tenant_id', $tenantId)
                ->where('employment_status', 'active')
                ->with(['department', 'designation'])
                ->orderBy('joining_date')
                ->take(1)
                ->get();
        }

        return $roots->map(fn ($emp) => $this->formatNode($emp))->toArray();
    }

    public function getChildrenNodes(string $managerId, int $depth = 1): array
    {
        $children = Employee::where('reporting_manager_id', $managerId)
            ->where('employment_status', 'active')
            ->with(['department', 'designation'])
            ->get();

        return $children->map(function ($child) use ($depth) {
            $formatted = $this->formatNode($child);
            if ($depth > 1) {
                $formatted['children'] = $this->getChildrenNodes($child->id, $depth - 1);
            }
            return $formatted;
        })->toArray();
    }

    public function getFocusedSubtree(Employee $employee): array
    {
        $employee->load(['department', 'designation', 'reportingManager.department', 'reportingManager.designation']);

        $selfNode = $this->formatNode($employee);
        $selfNode['children'] = $this->getChildrenNodes($employee->id, 1);

        $managerNode = null;
        if ($employee->reportingManager) {
            $managerNode = $this->formatNode($employee->reportingManager);
            $managerNode['focused_child'] = $selfNode;
        }

        return [
            'focus_employee_id' => $employee->id,
            'manager_node' => $managerNode,
            'employee_node' => $selfNode,
        ];
    }

    protected function formatNode(Employee $emp): array
    {
        $spanOfControl = Employee::where('reporting_manager_id', $emp->id)
            ->where('employment_status', 'active')
            ->count();

        return [
            'id' => $emp->id,
            'name' => $emp->fullName(),
            'employee_code' => $emp->employee_code,
            'job_title' => $emp->designation?->name ?? 'Staff Member',
            'department' => $emp->department?->name ?? 'General',
            'photo_path' => $emp->photo_path,
            'span_of_control' => $spanOfControl,
            'has_children' => $spanOfControl > 0,
        ];
    }
}
