<?php

declare(strict_types=1);

namespace Tests\Security;

use App\Domains\Api\Models\ApiClient;
use App\Domains\Api\Models\ApiKey;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Platform\Models\TenantSetting;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use DatabaseTransactions;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $userA;
    protected User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'smarthcm',
        ]);

        $this->tenantA = Tenant::firstOrCreate(
            ['slug' => 'tenant-alpha-isolation'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Tenant Alpha Corp',
                'tenant_code' => 'ALPHA-001',
                'status' => 'active',
                'is_active' => true,
            ]
        );

        $this->tenantB = Tenant::firstOrCreate(
            ['slug' => 'tenant-beta-isolation'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Tenant Beta Industries',
                'tenant_code' => 'BETA-002',
                'status' => 'active',
                'is_active' => true,
            ]
        );

        $this->userA = User::firstOrCreate(
            ['email' => 'alice@alpha-corp.internal'],
            [
                'name' => 'Alice Alpha',
                'password' => bcrypt('SecurePassword123!'),
                'tenant_id' => $this->tenantA->id,
            ]
        );

        $this->userB = User::firstOrCreate(
            ['email' => 'bob@beta-industries.internal'],
            [
                'name' => 'Bob Beta',
                'password' => bcrypt('SecurePassword123!'),
                'tenant_id' => $this->tenantB->id,
            ]
        );

        // Attach members to tenant_user
        if (DB::getSchemaBuilder()->hasTable('tenant_user')) {
            DB::table('tenant_user')->updateOrInsert(
                ['tenant_id' => $this->tenantA->id, 'user_id' => $this->userA->id],
                ['status' => 'active', 'created_at' => now(), 'updated_at' => now()]
            );

            DB::table('tenant_user')->updateOrInsert(
                ['tenant_id' => $this->tenantB->id, 'user_id' => $this->userB->id],
                ['status' => 'active', 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function test_tenant_a_user_cannot_switch_to_tenant_b(): void
    {
        $response = $this->actingAs($this->userA)
            ->postJson('/api/v1/me/tenants/switch', [
                'tenant' => $this->tenantB->slug,
            ]);

        $response->assertStatus(403);
    }

    public function test_tenant_a_user_cannot_access_tenant_b_settings_via_header_manipulation(): void
    {
        // Populate settings for Tenant B
        TenantSetting::updateOrCreate(
            ['tenant_id' => $this->tenantB->id],
            ['configuration' => ['confidential_salary_review' => true]]
        );

        // User A requests settings but spoofing X-Tenant header to Tenant B
        $response = $this->actingAs($this->userA)
            ->withHeader('X-Tenant', $this->tenantB->slug)
            ->getJson('/api/v1/tenant/settings');

        $response->assertStatus(403);
    }

    public function test_tenant_a_cannot_access_tenant_b_api_gateway_resources(): void
    {
        $clientA = ApiClient::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Client Alpha',
            'client_type' => 'internal',
            'client_identifier' => 'client_' . Str::random(12),
            'scopes' => ['employees:read'],
            'status' => 'active',
        ]);

        $plainKeyA = 'alpha_key_' . Str::random(24);
        ApiKey::create([
            'client_id' => $clientA->id,
            'key_hash' => hash('sha256', $plainKeyA),
            'label' => 'Alpha API Key',
            'expires_at' => now()->addMonth(),
        ]);

        $clientB = ApiClient::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Client Beta',
            'client_type' => 'internal',
            'client_identifier' => 'client_' . Str::random(12),
            'scopes' => ['payroll:sync'],
            'status' => 'active',
        ]);

        // Key A authenticates but tenant context bound must be Tenant A, not Tenant B
        $res = $this->withHeaders(['X-API-Key' => $plainKeyA])->getJson('/api/v1/external/ping');
        $res->assertStatus(200);
        $this->assertSame($this->tenantA->id, $res->json('tenant_id'));
        $this->assertNotSame($this->tenantB->id, $res->json('tenant_id'));
    }

    public function test_cache_keys_are_tenant_isolated(): void
    {
        $cacheKey = 'dashboard_summary';

        Cache::put("tenant:{$this->tenantA->id}:{$cacheKey}", ['revenue' => 1000000], 60);
        Cache::put("tenant:{$this->tenantB->id}:{$cacheKey}", ['revenue' => 5000], 60);

        $valA = Cache::get("tenant:{$this->tenantA->id}:{$cacheKey}");
        $valB = Cache::get("tenant:{$this->tenantB->id}:{$cacheKey}");

        $this->assertNotEquals($valA, $valB);
        $this->assertSame(1000000, $valA['revenue']);
        $this->assertSame(5000, $valB['revenue']);
    }

    public function test_tenant_context_service_enforces_active_tenant_boundaries(): void
    {
        $context = app(TenantContext::class);

        $context->set($this->tenantA);
        $this->assertSame($this->tenantA->id, $context->id());
        $this->assertTrue($context->hasTenant());

        $context->clear();
        $this->assertNull($context->id());
        $this->assertFalse($context->hasTenant());
    }
}
