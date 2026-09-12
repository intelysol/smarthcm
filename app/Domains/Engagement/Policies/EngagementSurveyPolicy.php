<?php

namespace App\Domains\Engagement\Policies;

use App\Domains\Engagement\Models\EngagementSurvey;
use App\Models\User;

class EngagementSurveyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hcm.engagement.survey.view')
            || $user->hasPermissionTo('hcm.engagement.view');
    }

    public function view(User $user, EngagementSurvey $survey): bool
    {
        return $user->tenant_id === $survey->tenant_id && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hcm.engagement.survey.create')
            || $user->hasPermissionTo('hcm.engagement.manage');
    }

    public function update(User $user, EngagementSurvey $survey): bool
    {
        return $user->tenant_id === $survey->tenant_id && (
            $user->hasPermissionTo('hcm.engagement.survey.edit') || $user->hasPermissionTo('hcm.engagement.manage')
        );
    }

    public function publish(User $user, EngagementSurvey $survey): bool
    {
        return $user->tenant_id === $survey->tenant_id && (
            $user->hasPermissionTo('hcm.engagement.survey.publish') || $user->hasPermissionTo('hcm.engagement.manage')
        );
    }

    public function delete(User $user, EngagementSurvey $survey): bool
    {
        return $user->tenant_id === $survey->tenant_id && (
            $user->hasPermissionTo('hcm.engagement.survey.archive') || $user->hasPermissionTo('hcm.engagement.manage')
        );
    }
}
