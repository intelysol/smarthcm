<?php

namespace App\Domains\Performance\Policies;

use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;

class PerformanceCyclePolicy extends BasePolicy
{
    public function viewAny(User $user): bool { return $user->hasPermission('hcm.performance.cycle.view'); }
    public function view(User $user, PerformanceCycle $cycle): bool { return $this->belongsToSameTenant($user, $cycle) && $user->hasPermission('hcm.performance.cycle.view'); }
    public function create(User $user): bool { return $user->hasPermission('hcm.performance.cycle.manage'); }
    public function update(User $user, PerformanceCycle $cycle): bool { return $this->belongsToSameTenant($user, $cycle) && $user->hasPermission('hcm.performance.cycle.manage'); }
    public function publish(User $user, PerformanceCycle $cycle): bool { return $this->belongsToSameTenant($user, $cycle) && $user->hasPermission('hcm.performance.cycle.publish'); }
}
