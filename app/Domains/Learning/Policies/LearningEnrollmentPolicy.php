<?php

namespace App\Domains\Learning\Policies;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;

class LearningEnrollmentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('hcm.learning.enrollment.view') || $user->hasPermission('hcm.learning.view');
    }

    public function view(User $user, LearningEnrollment $enrollment): bool
    {
        if (! $this->belongsToSameTenant($user, $enrollment)) {
            return false;
        }

        // Employee seeing their own enrollment
        $employee = Employee::query()->where('user_id', $user->id)->first();
        if ($employee && (string) $enrollment->employee_id === (string) $employee->id) {
            return true;
        }

        return $user->hasPermission('hcm.learning.enrollment.view') || $user->hasPermission('hcm.learning.enrollment.manage');
    }

    public function enroll(User $user): bool
    {
        return $user->hasPermission('hcm.learning.enroll') || $user->hasPermission('hcm.learning.enrollment.manage');
    }

    public function manage(User $user, LearningEnrollment $enrollment): bool
    {
        return $this->belongsToSameTenant($user, $enrollment) && $user->hasPermission('hcm.learning.enrollment.manage');
    }
}
