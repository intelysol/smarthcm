<?php

namespace App\Domains\Learning\Policies;

use App\Domains\Learning\Models\LearningSession;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;

class LearningSessionPolicy extends BasePolicy
{
    public function view(User $user, LearningSession $session): bool
    {
        return $this->belongsToSameTenant($user, $session) &&
            ($user->hasPermission('hcm.learning.session.view') || $user->hasPermission('hcm.learning.view'));
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('hcm.learning.session.manage');
    }

    public function attendance(User $user): bool
    {
        return $user->hasPermission('hcm.learning.session.attendance');
    }
}
