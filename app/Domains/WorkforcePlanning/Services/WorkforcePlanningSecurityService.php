<?php

namespace App\Domains\WorkforcePlanning\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class WorkforcePlanningSecurityService
{
    public function authorizePlanAccess(User $user, HcmWorkforcePlan $plan): void
    {
        // 1. Tenant boundary
        if ($user->tenant_id && $user->tenant_id !== $plan->tenant_id) {
            throw new AuthorizationException('Cross-tenant workforce plan access prohibited.');
        }

        // 2. Platform admins or users with global planning permissions have full access
        if ($user->is_platform_admin) {
            return;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('super-admin') || $user->hasRole('admin'))) {
            return;
        }

        if (method_exists($user, 'hasPermission') && $user->hasPermission('hcm.workforce_planning.manage')) {
            return;
        }

        // 3. Manager organizational scope enforcement
        $employee = Employee::where('user_id', $user->id)->first();
        if ($employee && $plan->department_id && $employee->department_id !== $plan->department_id) {
            // If plan is department-scoped and does not match manager's department
            throw new AuthorizationException('Access denied: You can only view workforce plans within your assigned department scope.');
        }
    }

    public function authorizeSensitiveBudgetAccess(User $user): void
    {
        if ($user->is_platform_admin) {
            return;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('super-admin') || $user->hasRole('admin'))) {
            return;
        }

        $canViewCost = method_exists($user, 'hasPermission') ? $user->hasPermission('hcm.workforce_cost.view') : false;
        $canViewSensitive = method_exists($user, 'hasPermission') ? $user->hasPermission('hcm.workforce_sensitive.view') : false;

        if (!$canViewCost && !$canViewSensitive) {
            throw new AuthorizationException('Access denied: Viewing granular compensation and labor cost plans requires elevated permissions.');
        }
    }
}
