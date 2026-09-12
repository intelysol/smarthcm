<?php

namespace App\Domains\Platform\Events;

use App\Domains\Platform\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class RoleAssigned
{
    use Dispatchable;

    public function __construct(public readonly Role $role, public readonly User $user, public readonly User $actor) {}
}
