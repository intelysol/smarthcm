<?php

namespace App\Domains\Learning\Policies;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningAssessment;
use App\Domains\Learning\Models\LearningAssessmentAttempt;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;

class LearningAssessmentPolicy extends BasePolicy
{
    public function view(User $user, LearningAssessment $assessment): bool
    {
        return $this->belongsToSameTenant($user, $assessment) &&
            ($user->hasPermission('hcm.learning.assessment.view') || $user->hasPermission('hcm.learning.view'));
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('hcm.learning.assessment.manage');
    }

    public function attempt(User $user, LearningAssessmentAttempt $attempt): bool
    {
        if (! $this->belongsToSameTenant($user, $attempt)) {
            return false;
        }

        $employee = Employee::query()->where('user_id', $user->id)->first();
        return $employee && (string) $attempt->employee_id === (string) $employee->id;
    }
}
