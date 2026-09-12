<?php

namespace App\Domains\Engagement\Policies;

use App\Domains\Engagement\Models\EngagementCampaign;
use App\Models\User;

class EngagementCampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hcm.engagement.campaign.view')
            || $user->hasPermissionTo('hcm.engagement.view');
    }

    public function view(User $user, EngagementCampaign $campaign): bool
    {
        return $user->tenant_id === $campaign->tenant_id && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hcm.engagement.campaign.manage')
            || $user->hasPermissionTo('hcm.engagement.manage');
    }

    public function update(User $user, EngagementCampaign $campaign): bool
    {
        return $user->tenant_id === $campaign->tenant_id && $this->create($user);
    }
}
