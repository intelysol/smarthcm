<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Contracts\TenantContext;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AuthorizationService
{
    public function __construct(private readonly TenantContext $tenant) {}

    /** @param array<string, mixed> $context */
    public function can(User $user, string $permission, mixed $resource = null, array $context = []): bool
    {
        $tenantId = $this->tenant->id() ?? $user->tenant_id;
        if ($tenantId === null) return false;
        $version = Cache::get("authz:version:{$tenantId}:{$user->id}", 1);
        $key = "authz:{$tenantId}:{$user->id}:{$version}:".sha1($permission);
        return Cache::remember($key, now()->addMinutes(5), function () use ($user, $tenantId, $permission): bool {
            $override = DB::table('permission_overrides')->join('permissions', 'permissions.id', '=', 'permission_overrides.permission_id')->where(['permission_overrides.tenant_id' => $tenantId, 'permission_overrides.user_id' => $user->id, 'permissions.name' => $permission])->where(fn ($q) => $q->whereNull('effective_from')->orWhere('effective_from', '<=', now()))->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', now()))->value('effect');
            if ($override === 'deny') return false;
            if ($override === 'allow') return true;
            if ($user->permissions()->where('name', $permission)->exists()) return true;
            return DB::table('user_roles')->join('role_permissions', 'role_permissions.role_id', '=', 'user_roles.role_id')->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')->where(['user_roles.user_id' => $user->id, 'user_roles.tenant_id' => $tenantId, 'permissions.name' => $permission])->where(fn ($q) => $q->whereNull('effective_from')->orWhere('effective_from', '<=', now()))->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', now()))->exists() || $user->roles()->whereHas('permissions', fn ($q) => $q->where('name', $permission))->exists();
        });
    }

    public function cannot(User $user, string $permission): bool { return ! $this->can($user, $permission); }
    public function authorizeOrFail(User $user, string $permission): void { if (! $this->can($user, $permission)) throw new AuthorizationException('Permission denied.'); }
    public function forget(User $user): void { Cache::increment("authz:version:".($this->tenant->id() ?? $user->tenant_id).":{$user->id}"); }
}
