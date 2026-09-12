<?php

namespace App\Domains\Engagement\Policies;

use App\Domains\Engagement\Models\EngagementActionPlan;
use App\Models\User;

class EngagementActionPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hcm.engagement.action_plan.view')
            || $user->hasPermissionTo('hcm.engagement.view');
    }

    public function view(User $user, EngagementActionPlan $plan): bool
    {
        return $user->tenant_id === $plan->tenant_id && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hcm.engagement.action_plan.manage')
            || $user->hasPermissionTo('hcm.engagement.manage');
    }

    public function update(User $user, EngagementActionPlan $plan): bool
    {
        return $user->tenant_id === $plan->tenant_id && $this->create($user);
    }
}
