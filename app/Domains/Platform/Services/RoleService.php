<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Events\RoleAssigned;
use App\Domains\Platform\Services\AuthorizationService;
use App\Domains\Platform\Models\Role;
use App\Domains\Shared\Services\ActivityLogService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RoleService
{
    public function __construct(private readonly ActivityLogService $activityLog, private readonly AuthorizationService $authorization) {}

    /** @param array<string, mixed> $attributes @param list<int> $permissionIds */
    public function create(string $tenantId, User $actor, array $attributes, array $permissionIds): Role
    {
        return DB::transaction(function () use ($tenantId, $actor, $attributes, $permissionIds): Role {
            $role = Role::query()->create([...$attributes, 'tenant_id' => $tenantId]);
            $role->permissions()->sync($permissionIds);
            $this->activityLog->record('role.created', $role, $actor->id, null, $role->attributesToArray());

            return $role->load('permissions');
        });
    }

    /** @param list<int> $permissionIds */
    public function syncPermissions(Role $role, User $actor, array $permissionIds): Role
    {
        $before = $role->permissions()->pluck('id')->all();
        $role->permissions()->sync($permissionIds);
        $this->activityLog->record('role.permissions_updated', $role, $actor->id, ['permission_ids' => $before], ['permission_ids' => $permissionIds]);

        return $role->load('permissions');
    }

    public function assign(Role $role, User $user, User $actor): void
    {
        abort_unless((string) $role->tenant_id === (string) $user->tenant_id, 422, 'Role and user must belong to the same tenant.');
        $role->users()->syncWithoutDetaching([$user->id => ['assigned_by' => $actor->id]]);
        DB::table('user_roles')->updateOrInsert(['user_id' => $user->id, 'tenant_id' => $role->tenant_id, 'role_id' => $role->id, 'scope' => 'tenant', 'scope_id' => null], ['id' => (string) \Illuminate\Support\Str::uuid(), 'assigned_by' => $actor->id, 'effective_from' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $this->authorization->forget($user);
        RoleAssigned::dispatch($role, $user, $actor);
    }
}
