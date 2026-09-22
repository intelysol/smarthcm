<?php

declare(strict_types=1);

namespace Tests\Feature\Workspace;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Enums\WorkspaceType;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkspaceExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected string $companyId;
    protected User $platformAdmin;
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
            'name' => 'Vanguard Experience Corp',
            'slug' => 'vanguard-experience',
            'tenant_code' => 'VNG-EXP',
            'status' => 'active',
        ]);

        $this->companyId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id' => $this->companyId,
            'tenant_id' => $this->tenant->id,
            'name' => 'Vanguard Global Corp',
            'legal_name' => 'Vanguard Global Corp Ltd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Platform Super Admin
        $this->platformAdmin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Global Super Admin',
            'email' => 'superadmin@vanguard.internal',
            'password' => bcrypt('Password123!'),
            'is_platform_admin' => true,
            'status' => 'active',
        ]);

        // 2. HR Admin
        $this->hrAdmin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'HR Operations Lead',
            'email' => 'hr@vanguard.internal',
            'password' => bcrypt('Password123!'),
            'status' => 'active',
        ]);

        // 3. Manager User & Employee
        $this->managerUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Engineering Lead',
            'email' => 'lead.manager@vanguard.internal',
            'password' => bcrypt('Password123!'),
            'status' => 'active',
        ]);

        $this->managerEmployee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->managerUser->id,
            'company_id' => $this->companyId,
            'employee_number' => 'MGR-001',
            'employee_code' => 'MGR-001',
            'first_name' => 'Engineering',
            'last_name' => 'Lead',
            'official_email' => 'lead.manager@vanguard.internal',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subYears(3)->toDateString(),
        ]);

        // 4. Standard Employee (reports to manager)
        $this->employeeUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Staff Developer',
            'email' => 'developer@vanguard.internal',
            'password' => bcrypt('Password123!'),
            'status' => 'active',
        ]);

        $this->standardEmployee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employeeUser->id,
            'company_id' => $this->companyId,
            'employee_number' => 'DEV-101',
            'employee_code' => 'DEV-101',
            'first_name' => 'Staff',
            'last_name' => 'Developer',
            'official_email' => 'developer@vanguard.internal',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subMonths(8)->toDateString(),
            'reporting_manager_id' => $this->managerEmployee->id,
        ]);
    }

    /**
     * Test 1: Employee Self-Service Home renders personal workspace without administrative menus
     */
    public function test_employee_home_renders_clean_personal_workspace(): void
    {
        $response = $this->actingAs($this->employeeUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/employee/home');

        $response->assertStatus(200);
        $response->assertSee('Welcome back, Staff');
        $response->assertSee("Today's Work & Shift", false);
        $response->assertSee('My Leave Balances');
        $response->assertSee('My Pending Tasks');

        // Crucial: Must NOT expose administrative items in employee view
        $response->assertDontSee('Platform Control Center');
        $response->assertDontSee('HR Command Center');
        $response->assertDontSee('Managed Enterprise Tenants');
    }

    /**
     * Test 2: Manager receives Team Workbench with direct reports roster
     */
    public function test_manager_workbench_renders_team_roster_and_approvals(): void
    {
        $response = $this->actingAs($this->managerUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/manager/workbench');

        $response->assertStatus(200);
        $response->assertSee('Team Workbench');
        $response->assertSee('Direct Reports Status');
        $response->assertSee('Staff Developer');
    }

    /**
     * Test 3: HR Admin receives HR Command Center with operational headcount and requisitions
     */
    public function test_hr_admin_renders_hr_command_center(): void
    {
        $response = $this->actingAs($this->hrAdmin)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/hr/dashboard');

        $response->assertStatus(200);
        $response->assertSee('HR Operations Command Center');
        $response->assertSee('Workforce Operations & Governance', false);
        $response->assertSee('Total Headcount');
    }

    /**
     * Test 4: Platform Admin receives Platform Control Center with multi-tenant metrics
     */
    public function test_platform_admin_renders_platform_control_center(): void
    {
        $response = $this->actingAs($this->platformAdmin)
            ->get('/platform/control-center');

        $response->assertStatus(200);
        $response->assertSee('Platform Control Center');
        $response->assertSee('Managed Enterprise Tenants');
        $response->assertSee('Vanguard Experience Corp');
    }

    /**
     * Test 5: Workspace switcher transitions session and redirects to target dashboard
     */
    public function test_workspace_switcher_transitions_and_updates_session(): void
    {
        // Manager switches to Employee workspace
        $response = $this->actingAs($this->managerUser)
            ->post('/workspace/switch', [
                'workspace' => WorkspaceType::EMPLOYEE->value,
            ]);

        $response->assertRedirect('/employee/home');
        $this->assertEquals(WorkspaceType::EMPLOYEE->value, session('active_workspace'));

        // Manager switches back to Manager workspace
        $response2 = $this->actingAs($this->managerUser)
            ->post('/workspace/switch', [
                'workspace' => WorkspaceType::MANAGER->value,
            ]);

        $response2->assertRedirect('/manager/workbench');
        $this->assertEquals(WorkspaceType::MANAGER->value, session('active_workspace'));
    }

    /**
     * Test 6: Workspace status API returns active and allowed workspaces payload
     */
    public function test_workspace_status_api_returns_structured_payload(): void
    {
        $response = $this->actingAs($this->managerUser)
            ->getJson('/workspace/status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'authenticated',
            'active_workspace' => ['type', 'label', 'icon', 'route_prefix'],
            'allowed_workspaces',
            'navigation',
        ]);
        $this->assertTrue($response->json('authenticated'));
    }
}
