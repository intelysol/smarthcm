<?php

namespace App\Domains\Offboarding\Services;

use App\Domains\Offboarding\Models\SeparationRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class SeparationSecurityService
{
    public function authorizeRequestAccess(User $user, SeparationRequest $request): void
    {
        // 1. Multi-tenant isolation
        if ((string) $user->tenant_id !== (string) $request->tenant_id) {
            throw new AuthorizationException('Cross-tenant offboarding access prohibited.');
        }

        // 2. Employee self-service check (including post-exit portal)
        if (!empty($user->employee_id) && (string) $user->employee_id === (string) $request->employee_id) {
            return;
        }

        // 3. Platform Admin or HR Administrator check
        $isAuthorized = $user->is_platform_admin
            || (string) $user->id === (string) $request->requested_by
            || (string) $user->id === (string) $request->approved_by
            || (method_exists($user, 'hasPermission') && $user->hasPermission('separations.view'));

        if (!$isAuthorized) {
            // Check reporting manager hierarchy
            $employee = $request->employee;
            if ($employee && (string) $employee->reporting_manager_id === (string) $user->employee_id) {
                return;
            }

            throw new AuthorizationException('Access denied: You are not authorized to view this separation record.');
        }
    }

    public function maskErCaseIfUnauthorized(User $user, ?string $erCaseReferenceId): ?string
    {
        if (empty($erCaseReferenceId)) {
            return null;
        }

        $canViewEr = $user->is_platform_admin
            || (method_exists($user, 'hasPermission') && $user->hasPermission('separations.view_er_cases'));

        if ($canViewEr) {
            return $erCaseReferenceId;
        }

        return 'REF-CONFIDENTIAL';
    }
}
