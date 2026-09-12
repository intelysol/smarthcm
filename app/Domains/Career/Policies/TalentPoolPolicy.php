<?php

namespace App\Domains\Career\Policies;

use App\Domains\Career\Models\TalentPool;
use App\Models\User;

class TalentPoolPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hcm.talent.pool.view') || $user->hasPermissionTo('hcm.talent.view');
    }

    public function view(User $user, TalentPool $pool): bool
    {
        return $user->hasPermissionTo('hcm.talent.pool.view') || $user->hasPermissionTo('hcm.talent.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hcm.talent.pool.manage') || $user->hasPermissionTo('hcm.talent.manage');
    }

    public function update(User $user, TalentPool $pool): bool
    {
        return $user->hasPermissionTo('hcm.talent.pool.manage') || $user->hasPermissionTo('hcm.talent.manage');
    }

    public function delete(User $user, TalentPool $pool): bool
    {
        return $user->hasPermissionTo('hcm.talent.pool.manage') || $user->hasPermissionTo('hcm.talent.manage');
    }
}
