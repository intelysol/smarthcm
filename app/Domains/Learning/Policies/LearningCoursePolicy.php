<?php

namespace App\Domains\Learning\Policies;

use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;

class LearningCoursePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('hcm.learning.course.view') || $user->hasPermission('hcm.learning.view');
    }

    public function view(User $user, LearningCourse $course): bool
    {
        return $this->belongsToSameTenant($user, $course) &&
            ($user->hasPermission('hcm.learning.course.view') || $user->hasPermission('hcm.learning.view'));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('hcm.learning.course.create');
    }

    public function update(User $user, LearningCourse $course): bool
    {
        return $this->belongsToSameTenant($user, $course) && $user->hasPermission('hcm.learning.course.edit');
    }

    public function publish(User $user, LearningCourse $course): bool
    {
        return $this->belongsToSameTenant($user, $course) && $user->hasPermission('hcm.learning.course.publish');
    }

    public function archive(User $user, LearningCourse $course): bool
    {
        return $this->belongsToSameTenant($user, $course) && $user->hasPermission('hcm.learning.course.archive');
    }
}
