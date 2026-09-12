<?php

namespace App\Domains\Platform\Policies;

use App\Domains\Platform\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('platform.roles.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('platform.roles.manage');
    }

    public function update(User $user, Role $role): bool
    {
        return ! $role->is_system && (string) $role->tenant_id === (string) $user->tenant_id && $user->hasPermission('platform.roles.manage');
    }

    public function delete(User $user, Role $role): bool
    {
        return $this->update($user, $role);
    }
}
