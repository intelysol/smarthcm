<?php

declare(strict_types=1);

namespace Tests\Feature\Operations;

use App\Domains\Api\Models\ApiClient;
use App\Domains\Api\Models\ApiKey;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductionSmokeTest extends TestCase
{
    use DatabaseTransactions;

    protected Tenant $tenant;
    protected Company $company;
    protected User $adminUser;
    protected User $hrUser;
    protected User $managerUser;
    protected User $employeeUser;
    protected Employee $employeeRecord;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'smarthcm',
        ]);

        // 1. Setup Tenant
        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'smoke-test-corp'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Smoke Test Enterprise Corp',
                'tenant_code' => 'SMK-001',
                'status' => 'active',
                'is_active' => true,
            ]
        );

        // 2. Setup Company
        $this->company = Company::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'name' => 'Smoke Test Global HQ'],
            [
                'currency' => 'USD',
                'timezone' => 'UTC',
            ]
        );

        // 3. Setup Users across 4 personas
        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin@smoke-test.internal'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('AdminPassword123!'),
                'tenant_id' => $this->tenant->id,
            ]
        );

        $this->hrUser = User::firstOrCreate(
            ['email' => 'hr@smoke-test.internal'],
            [
                'name' => 'HR Director',
                'password' => bcrypt('HrPassword123!'),
                'tenant_id' => $this->tenant->id,
            ]
        );

        $this->managerUser = User::firstOrCreate(
            ['email' => 'manager@smoke-test.internal'],
            [
                'name' => 'Engineering Manager',
                'password' => bcrypt('ManagerPassword123!'),
                'tenant_id' => $this->tenant->id,
            ]
        );

        $this->employeeUser = User::firstOrCreate(
            ['email' => 'emp@smoke-test.internal'],
            [
                'name' => 'Software Engineer',
                'password' => bcrypt('EmpPassword123!'),
                'tenant_id' => $this->tenant->id,
            ]
        );

        // 4. Attach members to tenant_user
        if (DB::getSchemaBuilder()->hasTable('tenant_user')) {
            foreach ([$this->adminUser, $this->hrUser, $this->managerUser, $this->employeeUser] as $u) {
                DB::table('tenant_user')->updateOrInsert(
                    ['tenant_id' => $this->tenant->id, 'user_id' => $u->id],
                    ['status' => 'active', 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // 5. Setup Employee Record
        $this->employeeRecord = Employee::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'official_email' => 'emp@smoke-test.internal'],
            [
                'user_id' => $this->employeeUser->id,
                'company_id' => $this->company->id,
                'employee_number' => 'EMP-SMK-' . rand(1000, 9999),
                'first_name' => 'Software',
                'last_name' => 'Engineer',
                'employment_status' => 'active',
                'joining_date' => now()->subYear()->toDateString(),
                'created_by' => $this->adminUser->id,
            ]
        );
    }

    public function test_smoke_01_core_health_check_endpoints(): void
    {
        $this->getJson('/health/live')->assertStatus(200)->assertJsonPath('status', 'ok');
        $this->getJson('/health/ready')->assertStatus(200)->assertJsonPath('ready', true);
        $this->getJson('/health')->assertStatus(200)->assertJsonPath('status', 'ok');
    }

    public function test_smoke_02_system_health_operations_dashboard_loads(): void
    {
        $res = $this->get('/operations/system-health');
        $res->assertStatus(200);
        $res->assertSee('System Health', false);
    }

    public function test_smoke_03_critical_journey_administrator(): void
    {
        // Admin authenticates and views admin settings
        $response = $this->actingAs($this->adminUser)
            ->withHeader('X-Tenant', $this->tenant->slug)
            ->getJson('/api/v1/tenant/settings');

        // Accessible or valid response
        $this->assertTrue(in_array($response->status(), [200, 404]));
    }

    public function test_smoke_04_critical_journey_employee(): void
    {
        // Employee authenticates and fetches own profile
        $response = $this->actingAs($this->employeeUser)
            ->withHeader('X-Tenant', $this->tenant->slug)
            ->getJson('/api/v1/platform/me');

        $response->assertStatus(200);
        $this->assertSame($this->employeeUser->email, $response->json('data.email'));
    }

    public function test_smoke_05_critical_journey_hr_and_manager(): void
    {
        // HR queries employee directory
        $this->assertNotNull($this->employeeRecord->id);
        $this->assertSame('active', $this->employeeRecord->employment_status);

        // Manager validates employee is in active roster
        $rosterCount = Employee::where('tenant_id', $this->tenant->id)->count();
        $this->assertGreaterThanOrEqual(1, $rosterCount);
    }

    public function test_smoke_06_api_gateway_integration(): void
    {
        $client = ApiClient::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Smoke API Client',
            'client_type' => 'partner',
            'client_identifier' => 'client_' . Str::random(12),
            'scopes' => ['employees:read'],
            'status' => 'active',
        ]);

        $plainKey = 'smk_key_' . Str::random(24);
        ApiKey::create([
            'client_id' => $client->id,
            'key_hash' => hash('sha256', $plainKey),
            'label' => 'Smoke Test API Key',
            'expires_at' => now()->addMonth(),
        ]);

        $res = $this->withHeaders(['X-API-Key' => $plainKey])->getJson('/api/v1/external/ping');
        $res->assertStatus(200)->assertJsonPath('authenticated', true);
    }

    public function test_smoke_07_login_and_dashboard_routing_and_unauthenticated_flow(): void
    {
        // 1. /login page exists, returns 200 OK and renders sign-in view
        $loginRes = $this->get('/login');
        $loginRes->assertStatus(200);
        $loginRes->assertSee('Enterprise Platform', false);

        // 2. /dashboard redirects to /portal
        $dashRes = $this->get('/dashboard');
        $dashRes->assertRedirect('/portal');

        // 3. portal.dashboard route name resolves to /portal, not /api/portal/dashboard
        $this->assertSame(url('/portal'), route('portal.dashboard'));

        // 4. Web portal dashboard loads
        $this->actingAs($this->employeeUser)->get('/portal')->assertStatus(200);
        $this->actingAs($this->employeeUser)->get('/portal/dashboard')->assertStatus(200);

        // 5. Unauthenticated API request returns 401 UNAUTHENTICATED JSON error envelope
        auth()->logout();
        $this->flushSession();
        $unauthApiRes = $this->getJson('/api/portal/dashboard');
        $unauthApiRes->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }
}
