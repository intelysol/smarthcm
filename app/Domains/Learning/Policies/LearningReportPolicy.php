<?php

namespace App\Domains\Learning\Policies;

use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;

class LearningReportPolicy extends BasePolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('hcm.learning.report.view');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('hcm.learning.report.export');
    }

    public function cost(User $user): bool
    {
        return $user->hasPermission('hcm.learning.cost.view');
    }
}
