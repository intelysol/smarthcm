<?php

namespace App\Domains\Engagement\Policies;

use App\Domains\Engagement\Models\CultureInitiative;
use App\Models\User;

class CultureInitiativePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hcm.engagement.culture.view')
            || $user->hasPermissionTo('hcm.engagement.view');
    }

    public function view(User $user, CultureInitiative $initiative): bool
    {
        return $user->tenant_id === $initiative->tenant_id && $this->viewAny($user);
    }

    public function manage(User $user): bool
    {
        return $user->hasPermissionTo('hcm.engagement.culture.manage')
            || $user->hasPermissionTo('hcm.engagement.manage');
    }
}
