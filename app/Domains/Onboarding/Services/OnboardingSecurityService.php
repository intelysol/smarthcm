<?php

namespace App\Domains\Onboarding\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class OnboardingSecurityService
{
    public function authorizeCaseAccess(User $user, HcmOnboardingCase $case): void
    {
        // 1. Multi-tenant isolation
        if ((string) $user->tenant_id !== (string) $case->tenant_id) {
            throw new AuthorizationException('Cross-tenant onboarding case access prohibited.');
        }

        // 2. Employee self-service check
        if (!empty($user->employee_id) && (string) $user->employee_id === (string) $case->employee_id) {
            return; // Authorized as the new hire employee
        }

        // 3. Platform Admin or HR / Manager check
        $isAuthorized = $user->is_platform_admin
            || (string) $user->id === (string) $case->owner_id
            || (method_exists($user, 'hasPermission') && $user->hasPermission('hcm.onboarding.manage'));

        if (!$isAuthorized) {
            // Check if user is the reporting manager of the employee
            $employee = $case->employee;
            if ($employee && (string) $employee->reporting_manager_id === (string) $user->employee_id) {
                return;
            }

            throw new AuthorizationException('Access denied: You are not authorized to view or manage this onboarding case.');
        }
    }

    public function maskSensitiveBankingData(array $formData): array
    {
        if (isset($formData['bank_account_number'])) {
            $raw = (string) $formData['bank_account_number'];
            $formData['bank_account_number'] = str_repeat('*', max(0, strlen($raw) - 4)) . substr($raw, -4);
        }

        if (isset($formData['tax_identification_number'])) {
            $raw = (string) $formData['tax_identification_number'];
            $formData['tax_identification_number'] = str_repeat('*', max(0, strlen($raw) - 4)) . substr($raw, -4);
        }

        return $formData;
    }
}
