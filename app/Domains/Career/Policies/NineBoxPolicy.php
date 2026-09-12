<?php

namespace App\Domains\Career\Policies;

use App\Models\User;

class NineBoxPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermissionTo('hcm.talent.matrix.view') ||
               $user->hasPermissionTo('hcm.talent.confidential.view') ||
               $user->hasPermissionTo('hcm.talent.manage');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermissionTo('hcm.talent.matrix.manage') ||
               $user->hasPermissionTo('hcm.talent.manage');
    }
}
