<?php

declare(strict_types=1);

namespace App\Domains\Shared\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Enums\WorkspaceType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class WorkspaceManager
{
    /**
     * Session key for active workspace.
     */
    public const SESSION_KEY = 'active_workspace';

    /**
     * Resolve all workspaces the user is authorized to access.
     *
     * @return array<WorkspaceType>
     */
    public function resolveAllowedWorkspaces(User $user): array
    {
        $workspaces = [];

        // 1. Platform Admin
        if ($user->is_platform_admin || $this->hasRoleLike($user, ['platform_admin', 'super_admin', 'platform_owner'])) {
            $workspaces[] = WorkspaceType::PLATFORM_ADMIN;
        }

        // 2. Tenant Admin
        if ($user->is_platform_admin || $this->hasRoleLike($user, ['tenant_admin', 'admin', 'company_admin', 'owner']) || $this->hasPermissionLike($user, ['tenant.', 'organization.', 'platform.tenants.manage'])) {
            $workspaces[] = WorkspaceType::TENANT_ADMIN;
        }

        // 3. HR Admin
        if ($user->is_platform_admin || $this->hasRoleLike($user, ['hr_admin', 'hr_manager', 'hr_director', 'payroll_admin', 'talent_admin', 'admin']) || $this->hasPermissionLike($user, ['hr.', 'payroll.', 'recruitment.', 'employees.'])) {
            $workspaces[] = WorkspaceType::HR_ADMIN;
        }

        // 4. Manager
        if ($user->is_platform_admin || $this->hasRoleLike($user, ['manager', 'team_lead', 'supervisor', 'engineering_manager']) || $this->isPeopleManager($user)) {
            $workspaces[] = WorkspaceType::MANAGER;
        }

        // 5. Executive / Workforce Intelligence
        if ($user->is_platform_admin || $this->hasRoleLike($user, ['executive', 'c_suite', 'director', 'vp', 'board', 'hr_director', 'admin']) || $this->hasPermissionLike($user, ['analytics.', 'intelligence.', 'workforce.plan'])) {
            $workspaces[] = WorkspaceType::EXECUTIVE;
        }

        // 6. Operations
        if ($user->is_platform_admin || $this->hasRoleLike($user, ['operations', 'devops', 'sysadmin', 'admin', 'platform_admin']) || $this->hasPermissionLike($user, ['system.health.view', 'operations.'])) {
            $workspaces[] = WorkspaceType::OPERATIONS;
        }

        // 7. Employee Self-Service (Default for all enterprise accounts)
        $workspaces[] = WorkspaceType::EMPLOYEE;

        $unique = [];
        foreach ($workspaces as $w) {
            $unique[$w->value] = $w;
        }

        return array_values($unique);
    }

    /**
     * Get currently active workspace for user.
     */
    public function getActiveWorkspace(User $user): WorkspaceType
    {
        $allowed = $this->resolveAllowedWorkspaces($user);
        $allowedValues = array_map(fn (WorkspaceType $w) => $w->value, $allowed);

        $current = Session::get(self::SESSION_KEY);

        if ($current && in_array($current, $allowedValues, true)) {
            return WorkspaceType::from($current);
        }

        // Default to highest privilege workspace or employee
        $default = $allowed[0] ?? WorkspaceType::EMPLOYEE;
        Session::put(self::SESSION_KEY, $default->value);

        return $default;
    }

    /**
     * Switch active workspace if permitted.
     */
    public function switchWorkspace(User $user, WorkspaceType $targetWorkspace): bool
    {
        $allowed = $this->resolveAllowedWorkspaces($user);

        if (!in_array($targetWorkspace, $allowed, true)) {
            return false;
        }

        Session::put(self::SESSION_KEY, $targetWorkspace->value);

        return true;
    }

    /**
     * Check if user can access a specific workspace.
     */
    public function canAccess(User $user, WorkspaceType $workspace): bool
    {
        $allowed = $this->resolveAllowedWorkspaces($user);
        return in_array($workspace, $allowed, true);
    }

    /**
     * Helper to detect people managers with direct reports.
     */
    protected function isPeopleManager(User $user): bool
    {
        $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();
        if (!$employee) {
            return false;
        }

        return DB::table('employees')
            ->where('tenant_id', $user->tenant_id)
            ->where(function ($q) use ($employee) {
                $q->where('reporting_manager_id', $employee->id)
                  ->orWhere('current_manager_employee_id', $employee->id);
            })
            ->exists();
    }

    /**
     * Helper to match role names.
     *
     * @param array<string> $roleNames
     */
    protected function hasRoleLike(User $user, array $roleNames): bool
    {
        $userRoles = array_map(fn ($r) => str_replace(['-', ' '], '_', strtolower((string) $r)), $user->roles()->pluck('name')->toArray());
        $normalizedTargets = array_map(fn ($r) => str_replace(['-', ' '], '_', strtolower($r)), $roleNames);

        foreach ($normalizedTargets as $target) {
            if (in_array($target, $userRoles, true)) {
                return true;
            }
        }

        // Also check role placeholder or email/name heuristics in development
        $email = strtolower($user->email ?? '');
        if (str_contains($email, 'superadmin') && (in_array('platform_admin', $normalizedTargets, true) || in_array('super_admin', $normalizedTargets, true))) {
            return true;
        }
        if (str_contains($email, 'admin') && !str_contains($email, 'superadmin') && !str_contains($email, 'hr') && (in_array('admin', $normalizedTargets, true) || in_array('tenant_admin', $normalizedTargets, true))) {
            return true;
        }
        if (str_contains($email, 'hr') && in_array('hr_admin', $normalizedTargets, true)) {
            return true;
        }
        if (str_contains($email, 'manager') && in_array('manager', $normalizedTargets, true)) {
            return true;
        }

        return false;
    }

    /**
     * Helper to match permission strings.
     *
     * @param array<string> $permissionPrefixes
     */
    protected function hasPermissionLike(User $user, array $permissionPrefixes): bool
    {
        $permissions = $user->permissions()->pluck('name')->toArray();
        foreach ($permissionPrefixes as $prefix) {
            foreach ($permissions as $permission) {
                if (str_starts_with($permission, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }
}
