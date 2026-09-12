<?php

namespace App\Domains\Career\Policies;

use App\Domains\Career\Models\CareerSkill;
use App\Domains\Career\Models\EmployeeSkill;
use App\Models\User;

class CareerSkillPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hcm.career.skills.view') || $user->hasPermissionTo('hcm.career.view');
    }

    public function view(User $user, CareerSkill $skill): bool
    {
        return $user->hasPermissionTo('hcm.career.skills.view') || $user->hasPermissionTo('hcm.career.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hcm.career.skills.manage');
    }

    public function update(User $user, CareerSkill $skill): bool
    {
        return $user->hasPermissionTo('hcm.career.skills.manage');
    }

    public function delete(User $user, CareerSkill $skill): bool
    {
        return $user->hasPermissionTo('hcm.career.skills.manage');
    }

    public function verify(User $user, EmployeeSkill $employeeSkill): bool
    {
        if ($user->hasPermissionTo('hcm.career.skills.verify') || $user->hasPermissionTo('hcm.career.manage')) {
            return true;
        }

        // Manager check
        $employee = $employeeSkill->employee;
        return $employee && $employee->reporting_manager_id && $employee->reportingManager?->user_id === $user->id;
    }
}
