<?php

namespace App\Domains\Career\Policies;

use App\Domains\Career\Models\SuccessionPlan;
use App\Models\User;

class SuccessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hcm.succession.view') ||
               $user->hasPermissionTo('hcm.talent.confidential.view') ||
               $user->hasPermissionTo('hcm.talent.manage');
    }

    public function view(User $user, SuccessionPlan $plan): bool
    {
        return $user->hasPermissionTo('hcm.succession.view') ||
               $user->hasPermissionTo('hcm.talent.confidential.view') ||
               $user->hasPermissionTo('hcm.talent.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hcm.succession.manage') || $user->hasPermissionTo('hcm.talent.manage');
    }

    public function update(User $user, SuccessionPlan $plan): bool
    {
        return $user->hasPermissionTo('hcm.succession.manage') || $user->hasPermissionTo('hcm.talent.manage');
    }

    public function calibrate(User $user): bool
    {
        return $user->hasPermissionTo('hcm.succession.calibration') ||
               $user->hasPermissionTo('hcm.succession.manage') ||
               $user->hasPermissionTo('hcm.talent.manage');
    }
}
