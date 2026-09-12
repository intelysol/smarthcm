<?php

namespace App\Domains\Engagement\Policies;

use App\Domains\Engagement\Models\EngagementResponse;
use App\Models\User;

class EngagementResponsePolicy
{
    public function view(User $user, EngagementResponse $response): bool
    {
        if ($user->tenant_id !== $response->tenant_id) {
            return false;
        }

        // If anonymous, individual response is never directly exposed to managers/profile
        if ($response->confidentiality_type === 'anonymous') {
            return $user->hasPermissionTo('hcm.engagement.confidential.view');
        }

        // If named, respondent or HR can view
        if ($response->employee && $response->employee->user_id === $user->id) {
            return true;
        }

        return $user->hasPermissionTo('hcm.engagement.results.view')
            || $user->hasPermissionTo('hcm.engagement.manage');
    }
}
