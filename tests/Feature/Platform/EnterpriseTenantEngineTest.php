<?php

namespace Tests\Feature\Platform;

use App\Domains\Platform\Enums\TenantStatus;
use App\Domains\Platform\Models\TenantSetting;
use App\Domains\Platform\Services\TenantLifecycleService;
use App\Domains\Platform\Services\TenantProvisioningService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnterpriseTenantEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_provisioning_is_idempotent_and_creates_isolated_defaults(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);
        $service = app(TenantProvisioningService::class);
        $tenant = $service->provision(['name' => 'Alpha', 'slug' => 'alpha', 'timezone' => 'UTC', 'currency' => 'USD'], $admin);
        $repeat = $service->provision(['name' => 'Alpha Changed', 'slug' => 'alpha', 'timezone' => 'UTC', 'currency' => 'USD'], $admin);

        $this->assertTrue($tenant->is($repeat));
        $this->assertDatabaseCount('tenants', 1);
        $this->assertDatabaseHas('tenant_user', ['tenant_id' => $tenant->id, 'user_id' => $admin->id, 'status' => 'active']);
        $this->assertDatabaseHas('tenant_settings', ['tenant_id' => $tenant->id]);
    }

    public function test_member_can_only_switch_to_an_authorized_tenant(): void
    {
        $alpha = Tenant::factory()->create();
        $beta = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $alpha->id]);
        $alpha->members()->attach($user->id, ['status' => 'active', 'joined_at' => now()]);

        $this->actingAs($user)->postJson('/api/v1/me/tenants/switch', ['tenant' => $beta->slug])->assertForbidden();
        $this->actingAs($user)->postJson('/api/v1/me/tenants/switch', ['tenant' => $alpha->slug])->assertOk()->assertJsonPath('data.id', $alpha->id);
    }

    public function test_lifecycle_transitions_are_enforced_and_audited(): void
    {
        $tenant = Tenant::factory()->create(['status' => TenantStatus::Active, 'is_active' => true]);
        $actor = User::factory()->create(['is_platform_admin' => true]);
        $service = app(TenantLifecycleService::class);
        $service->transition($tenant, TenantStatus::Suspended, $actor, 'Billing review');

        $this->assertDatabaseHas('tenants', ['id' => $tenant->id, 'status' => 'suspended', 'is_active' => false]);
        $this->assertDatabaseHas('tenant_audit_logs', ['tenant_id' => $tenant->id, 'action' => 'tenant.lifecycle.transition']);
    }

    public function test_tenant_configuration_cannot_be_read_with_a_foreign_context(): void
    {
        $alpha = Tenant::factory()->create();
        $beta = Tenant::factory()->create();
        TenantSetting::query()->create(['tenant_id' => $beta->id, 'configuration' => ['currency' => 'EUR']]);
        $user = User::factory()->create(['tenant_id' => $alpha->id]);
        $alpha->members()->attach($user->id, ['status' => 'active', 'joined_at' => now()]);

        $this->actingAs($user)->withHeader('X-Tenant', $beta->slug)->getJson('/api/v1/tenant/settings')->assertForbidden();
    }
}
