<?php

namespace Tests\Feature\Platform;

use App\Domains\Platform\Models\Role;
use App\Domains\Platform\Services\AuthorizationService;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_assignment_grants_only_its_configured_permission(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $group = PermissionGroup::query()->create(['name' => 'hcm', 'label' => 'HCM']);
        $allowed = Permission::query()->create(['permission_group_id' => $group->id, 'name' => 'hcm.employee.view', 'label' => 'View employees']);
        $role = Role::factory()->create(['tenant_id' => $tenant->id]);
        $role->permissions()->attach($allowed->id);
        DB::table('user_roles')->insert(['id' => (string) Str::uuid(), 'user_id' => $user->id, 'tenant_id' => $tenant->id, 'role_id' => $role->id, 'scope' => 'tenant', 'effective_from' => now(), 'created_at' => now(), 'updated_at' => now()]);

        $this->app->make(AuthorizationService::class)->forget($user);
        $this->assertTrue($user->hasPermission('hcm.employee.view'));
        $this->assertFalse($user->hasPermission('hcm.employee.delete'));
    }

    public function test_explicit_deny_overrides_a_role_grant(): void
    {
        $tenant = Tenant::factory()->create(); $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $group = PermissionGroup::query()->create(['name' => 'platform', 'label' => 'Platform']);
        $permission = Permission::query()->create(['permission_group_id' => $group->id, 'name' => 'platform.roles.manage', 'label' => 'Manage roles']);
        $role = Role::factory()->create(['tenant_id' => $tenant->id]); $role->permissions()->attach($permission->id);
        DB::table('user_roles')->insert(['id' => (string) Str::uuid(), 'user_id' => $user->id, 'tenant_id' => $tenant->id, 'role_id' => $role->id, 'scope' => 'tenant', 'effective_from' => now(), 'created_at' => now(), 'updated_at' => now()]);
        DB::table('permission_overrides')->insert(['id' => (string) Str::uuid(), 'tenant_id' => $tenant->id, 'user_id' => $user->id, 'permission_id' => $permission->id, 'effect' => 'deny', 'created_at' => now(), 'updated_at' => now()]);

        $this->app->make(AuthorizationService::class)->forget($user);
        $this->assertFalse($user->hasPermission('platform.roles.manage'));
    }
}
