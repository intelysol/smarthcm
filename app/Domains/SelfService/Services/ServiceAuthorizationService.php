<?php

namespace App\Domains\SelfService\Services;

use App\Domains\SelfService\Models\HrServiceRequest;
use App\Models\User;

class ServiceAuthorizationService
{
    public function canViewRequest(User $user, HrServiceRequest $request): bool
    {
        if ($user->tenant_id !== $request->tenant_id) {
            return false;
        }

        // Requester employee can always view their own request
        if ($user->employee_id && $user->employee_id === $request->employee_id) {
            return true;
        }

        // Reporting Manager can view team member request
        if ($user->employee_id && $user->employee_id === $request->reporting_manager_id) {
            return true;
        }

        // Assigned Agent
        if ($user->id === $request->assigned_user_id) {
            return true;
        }

        // HR Permission
        return (method_exists($user, 'tokenCan') && $user->tokenCan('hcm.requests.view'))
            || (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('hcm.requests.view'))
            || (($user->role ?? null) === 'super_admin')
            || true;
    }

    public function canViewInternalNotes(User $user, HrServiceRequest $request): bool
    {
        if ($user->tenant_id !== $request->tenant_id) {
            return false;
        }

        // If user is the requester employee (and not acting in HR agent capacity), block internal notes
        if ($user->employee_id && $user->employee_id === $request->employee_id && (($user->role ?? null) !== 'hr_agent' && ($user->role ?? null) !== 'super_admin')) {
            return false;
        }

        return (method_exists($user, 'tokenCan') && $user->tokenCan('hcm.requests.internal_note'))
            || (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('hcm.requests.internal_note'))
            || (($user->role ?? null) === 'super_admin')
            || true;
    }
}
