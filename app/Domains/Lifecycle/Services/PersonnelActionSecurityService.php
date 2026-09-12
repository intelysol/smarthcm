<?php

namespace App\Domains\Lifecycle\Services;

use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class PersonnelActionSecurityService
{
    public function authorizeRequestAccess(User $user, PersonnelActionRequest $request): void
    {
        // 1. Multi-tenant isolation
        if ((string) $user->tenant_id !== (string) $request->tenant_id) {
            throw new AuthorizationException('Cross-tenant personnel action access prohibited.');
        }

        // 2. Employee self-service check
        if (!empty($user->employee_id) && (string) $user->employee_id === (string) $request->employee_id) {
            return;
        }

        // 3. Platform Admin or HR Administrator check
        $isAuthorized = $user->is_platform_admin
            || (string) $user->id === (string) $request->requested_by
            || (string) $user->id === (string) $request->approved_by
            || (method_exists($user, 'hasPermission') && $user->hasPermission('personnel_actions.view'));

        if (!$isAuthorized) {
            // Check reporting manager hierarchy
            $employee = $request->employee;
            if ($employee && (string) $employee->reporting_manager_id === (string) $user->employee_id) {
                return;
            }

            throw new AuthorizationException('Access denied: You are not authorized to view this personnel action.');
        }
    }

    public function maskCompensationIfUnauthorized(User $user, array $changesData): array
    {
        $canViewCompensation = $user->is_platform_admin
            || (method_exists($user, 'hasPermission') && $user->hasPermission('personnel_actions.view_compensation'));

        if ($canViewCompensation) {
            return $changesData;
        }

        foreach ($changesData as &$change) {
            if (in_array($change['field_name'], ['base_salary', 'salary', 'basic_salary', 'bonus', 'allowance'])) {
                $change['old_value'] = '*** CONFIDENTIAL ***';
                $change['new_value'] = '*** CONFIDENTIAL ***';
                $change['old_value_label'] = '*** CONFIDENTIAL ***';
                $change['new_value_label'] = '*** CONFIDENTIAL ***';
            }
        }

        return $changesData;
    }
}
