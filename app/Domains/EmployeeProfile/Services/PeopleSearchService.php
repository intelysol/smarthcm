<?php

namespace App\Domains\EmployeeProfile\Services;

use App\Domains\Employee\Models\Employee;
use App\Models\User;

class PeopleSearchService
{
    public function search(User $viewer, string $term, int $limit = 10): array
    {
        $tenantId = $viewer->tenant_id;
        $term = trim($term);

        if (empty($term)) {
            return [];
        }

        $results = Employee::where('tenant_id', $tenantId)
            ->where('employment_status', 'active')
            ->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('employee_code', 'like', "%{$term}%")
                  ->orWhere('employee_number', 'like', "%{$term}%")
                  ->orWhere('official_email', 'like', "%{$term}%")
                  ->orWhereHas('designation', fn ($d) => $d->where('designation_name', 'like', "%{$term}%"))
                  ->orWhereHas('department', fn ($dept) => $dept->where('department_name', 'like', "%{$term}%"));
            })
            ->with(['department', 'designation', 'workLocation', 'branch'])
            ->take($limit)
            ->get();

        return $results->map(function ($emp) {
            return [
                'id' => $emp->id,
                'name' => $emp->fullName(),
                'employee_code' => $emp->employee_code,
                'job_title' => $emp->designation?->name ?? 'Employee',
                'department' => $emp->department?->name ?? 'General',
                'location' => $emp->workLocation?->name ?? $emp->city,
                'email' => $emp->official_email,
                'photo_path' => $emp->photo_path,
            ];
        })->toArray();
    }

    public function autocomplete(User $viewer, string $term, int $limit = 5): array
    {
        return $this->search($viewer, $term, $limit);
    }
}
