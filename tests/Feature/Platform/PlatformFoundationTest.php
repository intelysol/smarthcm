<?php

namespace Tests\Feature\Platform;

use App\Domains\Platform\Models\Role;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_limited_to_the_selected_active_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'email' => 'owner@example.test']);

        $this->postJson('/api/v1/platform/auth/login', ['tenant' => $tenant->slug, 'email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);

        $otherTenant = Tenant::factory()->create();
        $this->postJson('/api/v1/platform/auth/login', ['tenant' => $otherTenant->slug, 'email' => $user->email, 'password' => 'password'])
            ->assertUnauthorized();
    }

    public function test_role_creation_is_tenant_scoped_and_permission_protected(): void
    {
        $tenant = Tenant::factory()->create();
        $group = PermissionGroup::query()->create(['name' => 'platform', 'label' => 'Platform']);
        $manage = Permission::query()->create(['permission_group_id' => $group->id, 'name' => 'platform.roles.manage', 'label' => 'Manage roles']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->permissions()->attach($manage);

        $this->actingAs($user)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/platform/roles', ['name' => 'administrator', 'label' => 'Administrator', 'permission_ids' => [$manage->id]])
            ->assertCreated()
            ->assertJsonPath('data.name', 'administrator');

        $this->assertDatabaseHas('roles', ['tenant_id' => $tenant->id, 'name' => 'administrator']);
    }

    public function test_tenant_middleware_rejects_cross_tenant_requests(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)->withHeader('X-Tenant', $otherTenant->slug)
            ->getJson('/api/v1/platform/dashboard')
            ->assertForbidden();
    }

    public function test_role_permissions_extend_a_users_direct_permissions(): void
    {
        $tenant = Tenant::factory()->create();
        $group = PermissionGroup::query()->create(['name' => 'platform', 'label' => 'Platform']);
        $permission = Permission::query()->create(['permission_group_id' => $group->id, 'name' => 'platform.audit.view', 'label' => 'View audit']);
        $role = Role::factory()->create(['tenant_id' => $tenant->id]);
        $role->permissions()->attach($permission);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $role->users()->attach($user);

        $this->assertTrue($user->hasPermission('platform.audit.view'));
    }
}
