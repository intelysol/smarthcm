<?php

namespace App\Domains\Performance\Policies;

use App\Domains\Performance\Models\PerformanceGoal;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;

class PerformanceGoalPolicy extends BasePolicy
{
    public function view(User $user, PerformanceGoal $goal): bool { return $this->belongsToSameTenant($user, $goal) && $user->hasPermission('hcm.performance.goal.view'); }
    public function create(User $user): bool { return $user->hasPermission('hcm.performance.goal.create'); }
    public function update(User $user, PerformanceGoal $goal): bool { return $this->belongsToSameTenant($user, $goal) && $user->hasPermission('hcm.performance.goal.edit'); }
}
