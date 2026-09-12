<?php

namespace App\Domains\EmployeeProfile\Services;

use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class EmployeeProfileSecurityService
{
    public function authorizeProfileAccess(User $viewer, Employee $employee): void
    {
        // 1. Tenant Isolation
        if ((string) $viewer->tenant_id !== (string) $employee->tenant_id) {
            throw new AuthorizationException('Cross-tenant employee profile access prohibited.');
        }

        // 2. Base View Permission
        if (!$viewer->is_platform_admin && method_exists($viewer, 'hasPermission')) {
            if (!$viewer->hasPermission('employee_profile.view') && (string) $viewer->employee_id !== (string) $employee->id) {
                throw new AuthorizationException('Unauthorized to view this employee profile.');
            }
        }
    }

    public function canViewCompensation(User $viewer, Employee $employee): bool
    {
        if ((string) $viewer->tenant_id !== (string) $employee->tenant_id) {
            return false;
        }

        if ($viewer->is_platform_admin) {
            return true;
        }

        // Platform HR or sensitive view permission
        if (method_exists($viewer, 'hasPermission') && $viewer->hasPermission('employee_profile.view_sensitive')) {
            return true;
        }

        return false;
    }

    public function canViewEr(User $viewer): bool
    {
        return $viewer->is_platform_admin || (method_exists($viewer, 'hasPermission') && $viewer->hasPermission('employee_documents.view_er'));
    }
}
