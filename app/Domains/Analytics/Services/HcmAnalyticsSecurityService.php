<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Employee\Models\Employee;
use App\Models\User;

class HcmAnalyticsSecurityService
{
    public function canViewPayrollAnalytics(User $user): bool
    {
        return (method_exists($user, 'tokenCan') && $user->tokenCan('hcm.analytics.payroll.view'))
            || (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('hcm.analytics.payroll.view'))
            || (($user->role ?? null) === 'super_admin');
    }

    public function canViewErAggregates(User $user): bool
    {
        return (method_exists($user, 'tokenCan') && $user->tokenCan('hcm.analytics.er.aggregate.view'))
            || (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('hcm.analytics.er.aggregate.view'))
            || (($user->role ?? null) === 'super_admin');
    }

    public function canViewSensitiveAnalytics(User $user): bool
    {
        return (method_exists($user, 'tokenCan') && $user->tokenCan('hcm.analytics.sensitive.view'))
            || (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('hcm.analytics.sensitive.view'))
            || (($user->role ?? null) === 'super_admin');
    }

    public function applyManagerScopeFilters(User $user, array $filters = []): array
    {
        // If user is a line manager (and not super_admin or HR executive), restrict query to their direct reports
        $employee = Employee::where('tenant_id', $user->tenant_id)->where('user_id', $user->id)->first();
        if ($employee && (($user->role ?? null) === 'manager')) {
            $filters['manager_id'] = $employee->id;
        }

        return $filters;
    }
}
