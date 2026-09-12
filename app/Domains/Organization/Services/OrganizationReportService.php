<?php

namespace App\Domains\Organization\Services;

use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\CostCenter;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Designation;
use App\Domains\Organization\Models\Holiday;
use App\Domains\Organization\Models\Shift;

class OrganizationReportService
{
    public function summary(string $tenantId): array
    {
        return [
            'branches' => Branch::query()->where('tenant_id', $tenantId)->count(),
            'departments' => Department::query()->where('tenant_id', $tenantId)->count(),
            'cost_centers' => CostCenter::query()->where('tenant_id', $tenantId)->count(),
            'designations' => Designation::query()->where('tenant_id', $tenantId)->count(),
            'active_shifts' => Shift::query()->where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'upcoming_holidays' => Holiday::query()->where('tenant_id', $tenantId)->whereDate('holiday_date', '>=', now()->toDateString())->count(),
        ];
    }

    public function departmentStructure(string $tenantId): array
    {
        return Department::query()
            ->where('tenant_id', $tenantId)
            ->with('childDepartments')
            ->whereNull('parent_department_id')
            ->orderBy('department_name')
            ->get()
            ->toArray();
    }
}
