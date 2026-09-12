<?php

namespace App\Domains\EmployeeProfile\Services;

use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeDirectoryService
{
    public function searchDirectory(User $viewer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $tenantId = $viewer->tenant_id;

        $query = Employee::where('tenant_id', $tenantId)
            ->with(['department', 'designation', 'branch', 'workLocation', 'reportingManager']);

        // 1. Text Search
        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('employee_code', 'like', "%{$term}%")
                  ->orWhere('employee_number', 'like', "%{$term}%")
                  ->orWhere('official_email', 'like', "%{$term}%")
                  ->orWhereHas('designation', fn ($d) => $d->where('designation_name', 'like', "%{$term}%"))
                  ->orWhereHas('department', fn ($dept) => $dept->where('department_name', 'like', "%{$term}%"));
            });
        }

        // 2. Department Filter
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        // 3. Branch Filter
        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        // 4. Location Filter
        if (!empty($filters['work_location_id'])) {
            $query->where('work_location_id', $filters['work_location_id']);
        }

        // 5. Manager Filter
        if (!empty($filters['reporting_manager_id'])) {
            $query->where('reporting_manager_id', $filters['reporting_manager_id']);
        }

        // 6. Employment Status Filter (default: active if not specified)
        if (!empty($filters['status'])) {
            $query->where('employment_status', $filters['status']);
        } else {
            $query->where('employment_status', 'active');
        }

        return $query->orderBy('first_name')->paginate($perPage);
    }
}
