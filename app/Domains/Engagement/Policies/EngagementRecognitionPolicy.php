<?php

namespace App\Domains\Engagement\Policies;

use App\Domains\Engagement\Models\EngagementRecognition;
use App\Models\User;

class EngagementRecognitionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hcm.engagement.recognition.view')
            || $user->hasPermissionTo('hcm.engagement.view');
    }

    public function create(User $user): bool
    {
        return true; // Any authenticated employee can send recognition
    }

    public function moderate(User $user): bool
    {
        return $user->hasPermissionTo('hcm.engagement.recognition.moderate')
            || $user->hasPermissionTo('hcm.engagement.manage');
    }
}
