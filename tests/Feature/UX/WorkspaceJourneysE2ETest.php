<?php

declare(strict_types=1);

namespace Tests\Feature\UX;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Enums\WorkspaceType;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkspaceJourneysE2ETest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected string $companyId;
    protected User $platformAdmin;
    protected User $tenantAdmin;
    protected User $hrAdmin;
    protected User $managerUser;
    protected User $employeeUser;
    protected Employee $managerEmployee;
    protected Employee $standardEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Enterprise Journeys Corp',
            'slug' => 'journeys-corp',
            'tenant_code' => 'JRN-CORP',
            'status' => 'active',
        ]);

        $this->companyId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id' => $this->companyId,
            'tenant_id' => $this->tenant->id,
            'name' => 'Enterprise Journeys Global',
            'legal_name' => 'Enterprise Journeys Global Ltd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Platform Super Admin
        $this->platformAdmin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Global Platform Admin',
            'email' => 'platform@journeys.internal',
            'password' => bcrypt('Password123!'),
            'is_platform_admin' => true,
            'status' => 'active',
        ]);

        // 2. Tenant Admin
        $this->tenantAdmin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Tenant Organization Admin',
            'email' => 'tenantadmin@journeys.internal',
            'password' => bcrypt('Password123!'),
            'status' => 'active',
        ]);

        // 3. HR Admin
        $this->hrAdmin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'HR Director',
            'email' => 'hr@journeys.internal',
            'password' => bcrypt('Password123!'),
            'status' => 'active',
        ]);

        // 4. Manager User
        $this->managerUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Operations Manager',
            'email' => 'manager@journeys.internal',
            'password' => bcrypt('Password123!'),
            'status' => 'active',
        ]);

        $this->managerEmployee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->managerUser->id,
            'company_id' => $this->companyId,
            'employee_number' => 'MGR-701',
            'employee_code' => 'MGR-701',
            'first_name' => 'Operations',
            'last_name' => 'Manager',
            'official_email' => 'manager@journeys.internal',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subYears(2)->toDateString(),
        ]);

        // 5. Standard Employee (Reports to Manager)
        $this->employeeUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Core Team Member',
            'email' => 'member@journeys.internal',
            'password' => bcrypt('Password123!'),
            'status' => 'active',
        ]);

        $this->standardEmployee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employeeUser->id,
            'company_id' => $this->companyId,
            'employee_number' => 'EMP-702',
            'employee_code' => 'EMP-702',
            'first_name' => 'Core',
            'last_name' => 'Member',
            'official_email' => 'member@journeys.internal',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subMonths(6)->toDateString(),
            'reporting_manager_id' => $this->managerEmployee->id,
        ]);
    }

    /**
     * Journey 1: Employee Self-Service Journey
     */
    public function test_employee_user_journey_end_to_end(): void
    {
        // 1. Home
        $homeRes = $this->actingAs($this->employeeUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/employee/home');
        $homeRes->assertStatus(200);
        $homeRes->assertSee('Welcome back, Core');
        $homeRes->assertSee('skip-link');

        // 2. Profile
        $profileRes = $this->actingAs($this->employeeUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/portal/profile');
        $profileRes->assertStatus(200);

        // 3. Requests
        $reqRes = $this->actingAs($this->employeeUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/portal/requests');
        $reqRes->assertStatus(200);

        // 4. Work & Schedule
        $schedRes = $this->actingAs($this->employeeUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/portal/work');
        $schedRes->assertStatus(200);
    }

    /**
     * Journey 2: Manager Workbench Journey
     */
    public function test_manager_workbench_journey_end_to_end(): void
    {
        // 1. Workbench
        $wbRes = $this->actingAs($this->managerUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/manager/workbench');
        $wbRes->assertStatus(200);
        $wbRes->assertSee('Team Workbench');
        $wbRes->assertSee('Core Member');

        // 2. Team Members Roster
        $membersRes = $this->actingAs($this->managerUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/manager/members');
        $membersRes->assertStatus(200);
        $membersRes->assertSee('Direct Reports', false);

        // 3. Performance
        $perfRes = $this->actingAs($this->managerUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/manager/performance');
        $perfRes->assertStatus(200);

        // 4. Analytics
        $analyticsRes = $this->actingAs($this->managerUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/manager/analytics');
        $analyticsRes->assertStatus(200);
    }

    /**
     * Journey 3: HR Administration Journey
     */
    public function test_hr_admin_journey_end_to_end(): void
    {
        $hrRes = $this->actingAs($this->hrAdmin)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/hr/dashboard');
        $hrRes->assertStatus(200);
        $hrRes->assertSee('HR Operations Command Center');
        $hrRes->assertSee('Workforce Operations & Governance', false);
    }

    /**
     * Journey 4: Tenant Admin Portal Journey
     */
    public function test_tenant_admin_journey_end_to_end(): void
    {
        // 1. Settings
        $settingsRes = $this->actingAs($this->tenantAdmin)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/admin/settings');
        $settingsRes->assertStatus(200);
        $settingsRes->assertSee('Organization Settings');

        // 2. Users
        $usersRes = $this->actingAs($this->tenantAdmin)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/admin/users');
        $usersRes->assertStatus(200);

        // 3. Departments
        $deptRes = $this->actingAs($this->tenantAdmin)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/admin/departments');
        $deptRes->assertStatus(200);

        // 4. Workflows
        $wfRes = $this->actingAs($this->tenantAdmin)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/admin/workflows');
        $wfRes->assertStatus(200);
    }

    /**
     * Journey 5: Platform Super Admin Control Center Journey
     */
    public function test_platform_super_admin_journey_end_to_end(): void
    {
        // 1. Control Center
        $ccRes = $this->actingAs($this->platformAdmin)
            ->get('/platform/control-center');
        $ccRes->assertStatus(200);
        $ccRes->assertSee('Platform Control Center');

        // 2. Tenants
        $tenantsRes = $this->actingAs($this->platformAdmin)
            ->get('/platform/tenants');
        $tenantsRes->assertStatus(200);
        $tenantsRes->assertSee('Managed Enterprise Tenants');

        // 3. Billing
        $billingRes = $this->actingAs($this->platformAdmin)
            ->get('/platform/billing');
        $billingRes->assertStatus(200);
        $billingRes->assertSee('Subscription', false);
        $billingRes->assertSee('Billing', false);

        // 4. Security
        $secRes = $this->actingAs($this->platformAdmin)
            ->get('/platform/security');
        $secRes->assertStatus(200);

        // 5. AI Governance
        $aiRes = $this->actingAs($this->platformAdmin)
            ->get('/platform/ai-governance');
        $aiRes->assertStatus(200);
    }

    /**
     * Journey 6: Executive Workspace Journey
     */
    public function test_executive_workspace_journey_end_to_end(): void
    {
        // 1. Overview
        $execRes = $this->actingAs($this->tenantAdmin)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/executive/overview');
        $execRes->assertStatus(200);
        $execRes->assertSee('Executive Workforce Intelligence');

        // 2. Costs
        $costsRes = $this->actingAs($this->tenantAdmin)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/executive/costs');
        $costsRes->assertStatus(200);
        $costsRes->assertSee('Total Workforce Cost Analytics');
    }

    /**
     * Journey 7: Operations Workspace Journey
     */
    public function test_operational_workspace_journey_end_to_end(): void
    {
        // 1. Queues
        $queuesRes = $this->actingAs($this->platformAdmin)
            ->get('/operations/queues');
        $queuesRes->assertStatus(200);
        $queuesRes->assertSee('Queue Management', false);

        // 2. Logs
        $logsRes = $this->actingAs($this->platformAdmin)
            ->get('/operations/logs');
        $logsRes->assertStatus(200);
        $logsRes->assertSee('Operational Logs', false);

        // 3. System Health
        $healthRes = $this->actingAs($this->platformAdmin)
            ->get('/operations/system-health');
        $healthRes->assertStatus(200);
        $healthRes->assertSee('System Health', false);
    }
}
