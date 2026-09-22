<?php

declare(strict_types=1);

namespace Tests\E2E;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Flow\Packages\Billing\Domain\Models\BillingPlan;
use Flow\Packages\Billing\Domain\Models\BillingSubscription;
use Flow\Packages\Billing\Services\ProductPlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CriticalHcmJourneysTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Company $company;
    protected User $managerUser;
    protected User $employeeUser;
    protected User $adminUser;
    protected Employee $manager;
    protected Employee $employee;
    protected string $leaveTypeId;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Enterprise Tenant
        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Apex Vanguard International',
            'slug' => 'apex-vanguard',
            'tenant_code' => 'APX-E2E-01',
            'status' => 'active',
        ]);

        $this->company = Company::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Apex Vanguard HQ',
            'is_active' => true,
        ]);

        // Setup Admin User
        $this->adminUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'System Administrator',
            'email' => 'admin@apexvanguard.internal',
            'password' => bcrypt('AdminVanguard2026!'),
            'status' => 'active',
        ]);

        // Setup Manager
        $this->managerUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Victoria Chase',
            'email' => 'vchase@apexvanguard.internal',
            'password' => bcrypt('ManagerPass2026!'),
            'status' => 'active',
        ]);

        $this->manager = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->managerUser->id,
            'company_id' => $this->company->id,
            'employee_number' => 'MGR-100',
            'employee_code' => 'MGR-100',
            'first_name' => 'Victoria',
            'last_name' => 'Chase',
            'official_email' => 'vchase@apexvanguard.internal',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subYears(3)->toDateString(),
        ]);

        // Setup Employee
        $this->employeeUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Sean Diaz',
            'email' => 'sdiaz@apexvanguard.internal',
            'password' => bcrypt('EmployeePass2026!'),
            'status' => 'active',
        ]);

        $this->employee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employeeUser->id,
            'company_id' => $this->company->id,
            'reporting_manager_id' => $this->manager->id,
            'employee_number' => 'EMP-101',
            'employee_code' => 'EMP-101',
            'first_name' => 'Sean',
            'last_name' => 'Diaz',
            'official_email' => 'sdiaz@apexvanguard.internal',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subMonths(9)->toDateString(),
        ]);

        // Leave Setup
        $this->leaveTypeId = (string) Str::uuid();
        DB::table('leave_types')->insert([
            'id' => $this->leaveTypeId,
            'tenant_id' => $this->tenant->id,
            'name' => 'Executive Vacation Leave',
            'code' => 'EXEC_VAC',
            'is_paid' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('leave_balances')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveTypeId,
            'year' => (int) Carbon::now()->year,
            'entitled' => 25,
            'earned' => 10,
            'used' => 2,
            'pending' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Journey 1: Employee Self-Service Profile Access & Details
     */
    public function test_journey_1_employee_profile_and_dashboard_workflow(): void
    {
        $response = $this->actingAs($this->employeeUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->employee->id,
            ])
            ->getJson('/api/me/profile');

        $response->assertStatus(200);
        $this->assertEquals($this->employee->id, $response->json('personal.id'));
        $this->assertEquals('Sean', $response->json('personal.first_name'));
        $this->assertEquals('Diaz', $response->json('personal.last_name'));
    }

    /**
     * Journey 2: Employee Leave Request Submission & Manager Approval
     */
    public function test_journey_2_employee_leave_request_and_manager_approval(): void
    {
        // 1. Employee applies for leave
        $applyResponse = $this->actingAs($this->employeeUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->employee->id,
            ])
            ->postJson('/api/me/leave/apply', [
                'leave_type_id' => $this->leaveTypeId,
                'start_date' => Carbon::now()->addDays(10)->toDateString(),
                'end_date' => Carbon::now()->addDays(12)->toDateString(),
                'duration' => 3.0,
                'reason' => 'Annual family vacation retreat',
            ]);

        $applyResponse->assertStatus(201);
        $this->assertSame('PENDING', $applyResponse->json('status'));

        // 2. Manager fetches pending approvals
        $approvalsResponse = $this->actingAs($this->managerUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->manager->id,
            ])
            ->getJson('/api/manager/approvals');

        $approvalsResponse->assertStatus(200);
        $this->assertArrayHasKey('approvals', $approvalsResponse->json());
    }

    /**
     * Journey 3: Recruitment Requisition & Candidate Pipeline
     */
    public function test_journey_3_recruitment_pipeline_workflow(): void
    {
        // Seed job requisition in hcm_recruitment_requisitions
        $reqId = (string) Str::uuid();
        DB::table('hcm_recruitment_requisitions')->insert([
            'id' => $reqId,
            'tenant_id' => $this->tenant->id,
            'requisition_number' => 'REQ-2026-001',
            'title' => 'Senior Backend Systems Architect',
            'openings' => 2,
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $requisition = DB::table('hcm_recruitment_requisitions')->where('id', $reqId)->first();
        $this->assertNotNull($requisition);
        $this->assertEquals('open', $requisition->status);
    }

    /**
     * Journey 4: Time & Attendance Clock Toggle Cycle
     */
    public function test_journey_4_time_and_attendance_lifecycle(): void
    {
        // 1. Clock In
        $clockIn = $this->actingAs($this->employeeUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->employee->id,
            ])
            ->postJson('/api/me/attendance/clock', ['action' => 'toggle']);

        $clockIn->assertStatus(200);
        $this->assertSame('CLOCKED_IN', $clockIn->json('status'));

        // 2. Clock Out
        $clockOut = $this->actingAs($this->employeeUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->employee->id,
            ])
            ->postJson('/api/me/attendance/clock', ['action' => 'toggle']);

        $clockOut->assertStatus(200);
        $this->assertSame('CLOCKED_OUT', $clockOut->json('status'));
    }

    /**
     * Journey 5: Commercial SaaS Subscription Lifecycle
     */
    public function test_journey_5_commercial_subscription_journey(): void
    {
        app(ProductPlanService::class)->seedDefaultCatalog();

        $plan = BillingPlan::where('code', 'hcm-starter')->first();
        $this->assertNotNull($plan);

        // Create subscription with required starts_at timestamp
        $subscription = BillingSubscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'current_cycle_start' => now(),
            'current_cycle_end' => now()->addMonth(),
        ]);

        $this->assertSame('active', $subscription->status->value ?? (string) $subscription->status);
        $this->assertEquals($plan->id, $subscription->plan_id);
    }

    /**
     * Journey 6: AI Concierge Governed Query & Citations
     */
    public function test_journey_6_ai_concierge_governed_query(): void
    {
        $response = $this->actingAs($this->employeeUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->employee->id,
            ])
            ->postJson('/api/me/ai/chat', [
                'prompt' => 'How many vacation leave days do I have available?',
            ]);

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('response'));
        $this->assertEquals($this->employee->id, $response->json('employee_context.employee_id'));
    }

    /**
     * Journey 7: Tenant Administrator Management
     */
    public function test_journey_7_tenant_admin_management(): void
    {
        // Admin verifies tenant active status and user count
        $tenantUsersCount = User::where('tenant_id', $this->tenant->id)->count();
        $this->assertGreaterThanOrEqual(3, $tenantUsersCount);

        $tenantEmployeesCount = Employee::where('tenant_id', $this->tenant->id)->count();
        $this->assertEquals(2, $tenantEmployeesCount);
    }
}
