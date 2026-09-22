<?php

namespace App\Domains\TenantAdmin\Services;

use App\Domains\TenantAdmin\Models\HcmTenantDelegation;
use App\Domains\TenantAdmin\Models\HcmTenantRoleTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TenantSecurityAdminService
{
    public function getRoleTemplates(?string $tenantId = null): array
    {
        $templates = HcmTenantRoleTemplate::where(function ($q) use ($tenantId) {
            $q->whereNull('tenant_id');
            if ($tenantId) {
                $q->orWhere('tenant_id', $tenantId);
            }
        })->get();

        if ($templates->isEmpty()) {
            $defaultTemplates = [
                ['code' => 'TENANT_ADMIN', 'name' => 'Tenant Administrator', 'perms' => ['*']],
                ['code' => 'HR_ADMIN', 'name' => 'HR Administrator', 'perms' => ['hr.*', 'employee.*', 'leave.*', 'attendance.*']],
                ['code' => 'HR_MANAGER', 'name' => 'HR Manager', 'perms' => ['hr.view', 'employee.view', 'leave.approve', 'performance.*']],
                ['code' => 'PAYROLL_ADMIN', 'name' => 'Payroll Administrator', 'perms' => ['payroll.*', 'compensation.*']],
                ['code' => 'RECRUITER', 'name' => 'Talent Recruiter', 'perms' => ['recruitment.*', 'candidates.*']],
                ['code' => 'LINE_MANAGER', 'name' => 'Line Manager', 'perms' => ['team.view', 'leave.approve', 'attendance.approve']],
                ['code' => 'EMPLOYEE', 'name' => 'Standard Employee', 'perms' => ['self_service.*', 'profile.view']],
            ];

            foreach ($defaultTemplates as $t) {
                HcmTenantRoleTemplate::create([
                    'tenant_id' => $tenantId,
                    'template_code' => $t['code'],
                    'name' => $t['name'],
                    'description' => "Standard role template for {$t['name']}",
                    'assigned_permissions' => $t['perms'],
                    'is_system_template' => true,
                ]);
            }

            $templates = HcmTenantRoleTemplate::where('tenant_id', $tenantId)->orWhereNull('tenant_id')->get();
        }

        return $templates->toArray();
    }

    public function updateUserStatus(string $userId, string $newStatus): User
    {
        $user = User::findOrFail($userId);
        $user->status = $newStatus;
        if ($newStatus === 'LOCKED') {
            $user->locked_at = Carbon::now();
        } elseif ($newStatus === 'ACTIVE') {
            $user->locked_at = null;
        }
        $user->save();

        return $user;
    }

    public function createDelegation(string $tenantId, array $data): HcmTenantDelegation
    {
        return HcmTenantDelegation::create([
            'tenant_id' => $tenantId,
            'delegator_user_id' => $data['delegator_user_id'],
            'delegate_user_id' => $data['delegate_user_id'],
            'scope' => $data['scope'] ?? 'APPROVALS',
            'permissions' => $data['permissions'] ?? ['leave.approve', 'attendance.approve'],
            'reason' => $data['reason'],
            'starts_at' => Carbon::parse($data['starts_at']),
            'ends_at' => Carbon::parse($data['ends_at']),
            'status' => 'ACTIVE',
            'created_by' => $data['created_by'] ?? null,
        ]);
    }

    public function revokeDelegation(string $tenantId, string $delegationId): bool
    {
        $delegation = HcmTenantDelegation::where('tenant_id', $tenantId)->findOrFail($delegationId);
        return $delegation->update(['status' => 'REVOKED']);
    }
}
