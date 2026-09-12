<?php

namespace App\Domains\Career\Policies;

use App\Domains\Career\Models\TalentReviewSession;
use App\Models\User;

class TalentReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hcm.talent.review.view') ||
               $user->hasPermissionTo('hcm.talent.confidential.view') ||
               $user->hasPermissionTo('hcm.talent.manage');
    }

    public function view(User $user, TalentReviewSession $session): bool
    {
        return $user->hasPermissionTo('hcm.talent.review.view') ||
               $user->hasPermissionTo('hcm.talent.confidential.view') ||
               $user->hasPermissionTo('hcm.talent.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hcm.talent.review.manage') || $user->hasPermissionTo('hcm.talent.manage');
    }

    public function update(User $user, TalentReviewSession $session): bool
    {
        return $user->hasPermissionTo('hcm.talent.review.manage') || $user->hasPermissionTo('hcm.talent.manage');
    }

    public function calibrate(User $user): bool
    {
        return $user->hasPermissionTo('hcm.talent.matrix.manage') ||
               $user->hasPermissionTo('hcm.talent.review.manage') ||
               $user->hasPermissionTo('hcm.talent.manage');
    }
}
