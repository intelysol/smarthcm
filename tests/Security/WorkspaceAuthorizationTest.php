<?php

declare(strict_types=1);

namespace Tests\Security;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Enums\WorkspaceType;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkspaceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected string $companyId;
    protected User $platformAdmin;
    protected User $standardEmployeeUser;
    protected Employee $standardEmployee;
    protected User $managerUser;
    protected Employee $managerEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Secured Workspace Corp',
            'slug' => 'secured-workspace-corp',
            'tenant_code' => 'SEC-WS-01',
            'status' => 'active',
        ]);

        $this->companyId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id' => $this->companyId,
            'tenant_id' => $this->tenant->id,
            'name' => 'Secured Workspace Ltd',
            'legal_name' => 'Secured Workspace Ltd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Platform Super Admin
        $this->platformAdmin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Global Platform Admin',
            'email' => 'admin@platform.security',
            'password' => bcrypt('SecuredAdmin2026!'),
            'is_platform_admin' => true,
            'status' => 'active',
        ]);

        // 2. Manager User & Employee
        $this->managerUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Operations Manager',
            'email' => 'manager@platform.security',
            'password' => bcrypt('SecuredManager2026!'),
            'status' => 'active',
        ]);

        $this->managerEmployee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->managerUser->id,
            'company_id' => $this->companyId,
            'employee_number' => 'MGR-SEC-01',
            'employee_code' => 'MGR-SEC-01',
            'first_name' => 'Operations',
            'last_name' => 'Manager',
            'official_email' => 'manager@platform.security',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subYears(2)->toDateString(),
        ]);

        // 3. Regular Employee (reporting to manager)
        $this->standardEmployeeUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Junior Analyst',
            'email' => 'analyst@platform.security',
            'password' => bcrypt('SecuredEmployee2026!'),
            'status' => 'active',
        ]);

        $this->standardEmployee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->standardEmployeeUser->id,
            'company_id' => $this->companyId,
            'employee_number' => 'EMP-SEC-02',
            'employee_code' => 'EMP-SEC-02',
            'first_name' => 'Junior',
            'last_name' => 'Analyst',
            'official_email' => 'analyst@platform.security',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subMonths(6)->toDateString(),
            'reporting_manager_id' => $this->managerEmployee->id,
        ]);
    }

    /**
     * Test 1: Unauthenticated visitors are redirected to /login across all workspaces.
     */
    public function test_unauthenticated_requests_are_redirected_to_login(): void
    {
        $protectedRoutes = [
            '/platform/control-center',
            '/admin/dashboard',
            '/hr/dashboard',
            '/manager/workbench',
            '/employee/home',
            '/executive/overview',
            '/operations/queues',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    /**
     * Test 2: Standard employee is denied access (HTTP 403) to administrative and privileged workspaces.
     */
    public function test_standard_employee_cannot_access_administrative_workspaces(): void
    {
        $restrictedWorkspaces = [
            '/platform/control-center',
            '/admin/dashboard',
            '/hr/dashboard',
            '/manager/workbench',
            '/executive/overview',
            '/operations/queues',
        ];

        foreach ($restrictedWorkspaces as $route) {
            $response = $this->actingAs($this->standardEmployeeUser)
                ->withSession(['tenant_uuid' => $this->tenant->id])
                ->get($route);

            $response->assertStatus(403);
            $response->assertSee('Access Restricted');
        }
    }

    /**
     * Test 3: Standard employee is permitted to access their employee workspace.
     */
    public function test_standard_employee_can_access_employee_workspace(): void
    {
        $response = $this->actingAs($this->standardEmployeeUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/employee/home');

        $response->assertStatus(200);
        $response->assertSee('Welcome back, Junior');
    }

    /**
     * Test 4: People Manager is permitted to access Manager Workbench and Employee Workspace, but denied Platform/Admin.
     */
    public function test_people_manager_can_access_manager_and_employee_but_denied_platform(): void
    {
        // Allowed
        $mgrResponse = $this->actingAs($this->managerUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/manager/workbench');
        $mgrResponse->assertStatus(200);

        $empResponse = $this->actingAs($this->managerUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/employee/home');
        $empResponse->assertStatus(200);

        // Forbidden
        $platResponse = $this->actingAs($this->managerUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/platform/control-center');
        $platResponse->assertStatus(403);

        $adminResponse = $this->actingAs($this->managerUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->get('/admin/dashboard');
        $adminResponse->assertStatus(403);
    }

    /**
     * Test 5: Standard employee cannot switch session to an unauthorized workspace.
     */
    public function test_unauthorized_workspace_switch_is_rejected_with_403(): void
    {
        $response = $this->actingAs($this->standardEmployeeUser)
            ->withSession(['tenant_uuid' => $this->tenant->id])
            ->post('/workspace/switch', [
                'workspace' => WorkspaceType::PLATFORM_ADMIN->value,
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test 6: Platform Super Admin can access all workspaces.
     */
    public function test_platform_super_admin_has_omnipresent_workspace_access(): void
    {
        $workspaces = [
            '/platform/control-center',
            '/admin/dashboard',
            '/hr/dashboard',
            '/executive/overview',
            '/operations/queues',
        ];

        foreach ($workspaces as $route) {
            $response = $this->actingAs($this->platformAdmin)
                ->withSession(['tenant_uuid' => $this->tenant->id])
                ->get($route);

            $response->assertStatus(200);
        }
    }
}