<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\AttendancePolicy;
use App\Domains\Attendance\Models\AttendancePolicyAssignment;
use App\Domains\Employee\Models\Employee;
use Carbon\Carbon;

class AttendancePolicyResolver
{
    /**
     * Resolves the effective attendance policy for an employee on a specific date.
     */
    public function resolve(Employee $employee, ?string $date = null): AttendancePolicy
    {
        $dateStr = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();
        $tenantId = $employee->tenant_id;

        // 1. Employee specific assignment
        $empAssignment = AttendancePolicyAssignment::query()
            ->where('tenant_id', $tenantId)
            ->where('scope_type', 'employee')
            ->where('scope_id', $employee->id)
            ->where('effective_from', '<=', $dateStr)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $dateStr))
            ->with('policy')
            ->first();

        if ($empAssignment && $empAssignment->policy && $empAssignment->policy->is_active) {
            return $empAssignment->policy;
        }

        // 2. Department specific assignment
        if ($employee->department_id) {
            $deptAssignment = AttendancePolicyAssignment::query()
                ->where('tenant_id', $tenantId)
                ->where('scope_type', 'department')
                ->where('scope_id', $employee->department_id)
                ->where('effective_from', '<=', $dateStr)
                ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $dateStr))
                ->with('policy')
                ->first();

            if ($deptAssignment && $deptAssignment->policy && $deptAssignment->policy->is_active) {
                return $deptAssignment->policy;
            }
        }

        // 3. Branch specific assignment
        if ($employee->branch_id) {
            $branchAssignment = AttendancePolicyAssignment::query()
                ->where('tenant_id', $tenantId)
                ->where('scope_type', 'branch')
                ->where('scope_id', $employee->branch_id)
                ->where('effective_from', '<=', $dateStr)
                ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $dateStr))
                ->with('policy')
                ->first();

            if ($branchAssignment && $branchAssignment->policy && $branchAssignment->policy->is_active) {
                return $branchAssignment->policy;
            }
        }

        // 4. Company specific assignment
        if ($employee->company_id) {
            $companyAssignment = AttendancePolicyAssignment::query()
                ->where('tenant_id', $tenantId)
                ->where('scope_type', 'company')
                ->where('scope_id', $employee->company_id)
                ->where('effective_from', '<=', $dateStr)
                ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $dateStr))
                ->with('policy')
                ->first();

            if ($companyAssignment && $companyAssignment->policy && $companyAssignment->policy->is_active) {
                return $companyAssignment->policy;
            }
        }

        // 5. Global Policy Assignment
        $globalAssignment = AttendancePolicyAssignment::query()
            ->where('tenant_id', $tenantId)
            ->where('scope_type', 'global')
            ->where('effective_from', '<=', $dateStr)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $dateStr))
            ->with('policy')
            ->first();

        if ($globalAssignment && $globalAssignment->policy && $globalAssignment->policy->is_active) {
            return $globalAssignment->policy;
        }

        // 6. Default active policy in tenant
        $fallbackPolicy = AttendancePolicy::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->first();

        if ($fallbackPolicy) {
            return $fallbackPolicy;
        }

        // In-memory default fallback
        return new AttendancePolicy([
            'tenant_id' => $tenantId,
            'code' => 'DEFAULT',
            'name' => 'Default Policy',
            'grace_period_minutes' => 15,
            'late_threshold_minutes' => 30,
            'early_departure_threshold_minutes' => 15,
            'half_day_late_threshold_minutes' => 120,
            'minimum_working_hours_minutes' => 240,
            'overtime_minimum_minutes' => 30,
            'overtime_approval_required' => true,
            'rounding_interval_minutes' => 1,
            'rounding_method' => 'nearest',
            'auto_deduct_breaks' => false,
            'missing_punch_policy' => 'flag_exception',
            'is_active' => true,
        ]);
    }
}
