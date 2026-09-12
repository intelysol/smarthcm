<?php

namespace App\Domains\Learning\Policies;

use App\Domains\Learning\Models\LearningRequirement;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;

class LearningRequirementPolicy extends BasePolicy
{
    public function view(User $user, LearningRequirement $requirement): bool
    {
        return $this->belongsToSameTenant($user, $requirement) &&
            ($user->hasPermission('hcm.learning.requirement.view') || $user->hasPermission('hcm.learning.view'));
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('hcm.learning.requirement.manage');
    }
}
