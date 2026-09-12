<?php

namespace App\Domains\Engagement\Policies;

use App\Domains\Engagement\Models\EngagementCampaign;
use App\Models\User;

class EngagementResultPolicy
{
    public function viewResults(User $user, EngagementCampaign $campaign): bool
    {
        return $user->tenant_id === $campaign->tenant_id && (
            $user->hasPermissionTo('hcm.engagement.results.view')
            || $user->hasPermissionTo('hcm.engagement.view')
            || $user->hasPermissionTo('hcm.engagement.anonymous.aggregate.view')
        );
    }

    public function exportResults(User $user, EngagementCampaign $campaign): bool
    {
        return $user->tenant_id === $campaign->tenant_id && (
            $user->hasPermissionTo('hcm.engagement.results.export')
            || $user->hasPermissionTo('hcm.engagement.manage')
        );
    }
}
