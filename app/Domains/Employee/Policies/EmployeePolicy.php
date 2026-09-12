<?php

namespace App\Domains\Employee\Policies;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;

class EmployeePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('employee.view');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $this->belongsToSameTenant($user, $employee) && $user->hasPermission('employee.view');
    }

    public function create(User $user): bool
    {
        return $user->tenant_id !== null && $user->hasPermission('employee.create');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $this->belongsToSameTenant($user, $employee) && $user->hasPermission('employee.update');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $this->belongsToSameTenant($user, $employee) && $user->hasPermission('employee.delete');
    }
}
