<?php

namespace App\Domains\Career\Policies;

use App\Domains\Career\Models\CareerPlan;
use App\Models\User;

class CareerPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hcm.career.plan.view') || $user->hasPermissionTo('hcm.career.view');
    }

    public function view(User $user, CareerPlan $plan): bool
    {
        // 1. Plan owner
        if ($plan->employee?->user_id === $user->id) {
            return true;
        }

        // 2. HR Admin
        if ($user->hasPermissionTo('hcm.career.plan.manage') || $user->hasPermissionTo('hcm.career.manage')) {
            return true;
        }

        // 3. Manager Visibility check
        if ($plan->visibility !== 'private') {
            $employee = $plan->employee;
            if ($employee && $employee->reportingManager?->user_id === $user->id) {
                return true;
            }
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hcm.career.plan.create') || $user->hasPermissionTo('hcm.career.view');
    }

    public function update(User $user, CareerPlan $plan): bool
    {
        if ($plan->employee?->user_id === $user->id && in_array($plan->status, ['draft', 'under_review', 'in_progress'], true)) {
            return true;
        }

        return $user->hasPermissionTo('hcm.career.plan.manage');
    }

    public function approve(User $user, CareerPlan $plan): bool
    {
        if ($user->hasPermissionTo('hcm.career.plan.approve') || $user->hasPermissionTo('hcm.career.plan.manage')) {
            return true;
        }

        $employee = $plan->employee;
        return $employee && $employee->reportingManager?->user_id === $user->id;
    }
}
