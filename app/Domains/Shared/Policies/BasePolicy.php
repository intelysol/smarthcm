<?php

namespace App\Domains\Shared\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class BasePolicy
{
    protected function belongsToSameTenant(User $user, Model $model): bool
    {
        return isset($model->tenant_id) && $user->tenant_id === $model->tenant_id;
    }
}
