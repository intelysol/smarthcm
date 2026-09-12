<?php

namespace App\Domains\Learning\Policies;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Shared\Policies\BasePolicy;
use App\Models\User;

class LearningCertificatePolicy extends BasePolicy
{
    public function view(User $user, LearningCertificate $certificate): bool
    {
        if (! $this->belongsToSameTenant($user, $certificate)) {
            return false;
        }

        $employee = Employee::query()->where('user_id', $user->id)->first();
        if ($employee && (string) $certificate->employee_id === (string) $employee->id) {
            return true;
        }

        return $user->hasPermission('hcm.learning.certificate.view') || $user->hasPermission('hcm.learning.certificate.manage');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('hcm.learning.certificate.manage');
    }

    public function verify(User $user): bool
    {
        return $user->hasPermission('hcm.learning.certificate.verify');
    }
}
