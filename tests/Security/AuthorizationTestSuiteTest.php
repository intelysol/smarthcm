<?php

declare(strict_types=1);

namespace Tests\Security;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthorizationTestSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Company $company;
    protected User $adminUser;
    protected User $hrUser;
    protected User $managerUser;
    protected User $employeeUser;
    protected Employee $managerEmployee;
    protected Employee $regularEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'OmniCorp Security Corp',
            'slug' => 'omnicorp-sec',
            'tenant_code' => 'OMNI-SEC-01',
            'status' => 'active',
        ]);

        $this->company = Company::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'OmniCorp Global HQ',
            'is_active' => true,
        ]);

        // 1. Admin User
        $this->adminUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Chief Admin',
            'email' => 'admin@omnicorp.internal',
            'password' => bcrypt('AdminPass2026!'),
            'status' => 'active',
        ]);

        // 2. HR Specialist User
        $this->hrUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'HR Specialist',
            'email' => 'hr@omnicorp.internal',
            'password' => bcrypt('HrPass2026!'),
            'status' => 'active',
        ]);

        // 3. Manager User & Record
        $this->managerUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Department Manager',
            'email' => 'manager@omnicorp.internal',
            'password' => bcrypt('ManagerPass2026!'),
            'status' => 'active',
        ]);

        $this->managerEmployee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->managerUser->id,
            'company_id' => $this->company->id,
            'employee_number' => 'MGR-500',
            'employee_code' => 'MGR-500',
            'first_name' => 'David',
            'last_name' => 'Brent',
            'official_email' => 'manager@omnicorp.internal',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subYears(3)->toDateString(),
        ]);

        // 4. Regular Employee User & Record
        $this->employeeUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Staff Employee',
            'email' => 'staff@omnicorp.internal',
            'password' => bcrypt('StaffPass2026!'),
            'status' => 'active',
        ]);

        $this->regularEmployee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employeeUser->id,
            'company_id' => $this->company->id,
            'reporting_manager_id' => $this->managerEmployee->id,
            'employee_number' => 'EMP-501',
            'employee_code' => 'EMP-501',
            'first_name' => 'Jim',
            'last_name' => 'Halpert',
            'official_email' => 'staff@omnicorp.internal',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subMonths(6)->toDateString(),
        ]);
    }

    public function test_employee_can_access_own_portal_endpoints(): void
    {
        $response = $this->actingAs($this->employeeUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->regularEmployee->id,
            ])
            ->getJson('/api/me/dashboard');

        $response->assertStatus(200);
        $this->assertEquals($this->regularEmployee->id, $response->json('employee.id'));
    }

    public function test_manager_can_access_manager_workbench_and_approvals(): void
    {
        $response = $this->actingAs($this->managerUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $this->managerEmployee->id,
            ])
            ->getJson('/api/manager/dashboard');

        $response->assertStatus(200);
        $this->assertArrayHasKey('team_summary', $response->json());
    }

    public function test_unauthenticated_request_to_manager_workbench_is_rejected(): void
    {
        $response = $this->getJson('/api/manager/dashboard');

        $response->assertStatus(401);
        $this->assertTrue(in_array($response->json('error.code'), ['UNAUTHENTICATED', 'HTTP_401']));
    }

    public function test_unauthorized_user_cannot_access_other_employee_payslip(): void
    {
        // Setup mock payslip for regular employee
        $payslipId = (string) Str::uuid();
        $periodId = (string) Str::uuid();
        $runId = (string) Str::uuid();
        $snapId = (string) Str::uuid();

        DB::table('payroll_periods')->insert([
            'id' => $periodId,
            'tenant_id' => $this->tenant->id,
            'period_name' => 'Test Period',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payroll_runs')->insert([
            'id' => $runId,
            'tenant_id' => $this->tenant->id,
            'payroll_period_id' => $periodId,
            'run_number' => 'RUN-TEST-01',
            'name' => 'Test Run',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payroll_calculation_snapshots')->insert([
            'id' => $snapId,
            'tenant_id' => $this->tenant->id,
            'payroll_run_id' => $runId,
            'employee_id' => $this->regularEmployee->id,
            'gross_pay' => 4500.00,
            'total_deductions' => 500.00,
            'net_pay' => 4000.00,
            'employee_snapshot' => json_encode(['id' => $this->regularEmployee->id]),
            'compensation_snapshot' => json_encode([]),
            'inputs_snapshot' => json_encode([]),
            'earnings_snapshot' => json_encode([]),
            'deductions_snapshot' => json_encode([]),
            'taxes_snapshot' => json_encode([]),
            'employer_contributions_snapshot' => json_encode([]),
            'idempotency_key' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payroll_payslips')->insert([
            'id' => $payslipId,
            'tenant_id' => $this->tenant->id,
            'payroll_run_id' => $runId,
            'employee_id' => $this->regularEmployee->id,
            'payroll_calculation_snapshot_id' => $snapId,
            'payslip_number' => 'PS-SEC-01',
            'pay_date' => '2026-09-30',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'gross_pay' => 4500.00,
            'total_deductions' => 500.00,
            'net_pay' => 4000.00,
            'currency' => 'USD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Another employee attempts to view regular employee's payslip
        $otherUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Snooping Employee',
            'email' => 'snoop@omnicorp.internal',
            'password' => bcrypt('Pass123!'),
            'status' => 'active',
        ]);
        $otherEmp = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $otherUser->id,
            'company_id' => $this->company->id,
            'employee_number' => 'EMP-599',
            'employee_code' => 'EMP-599',
            'first_name' => 'Snooper',
            'last_name' => 'Spy',
            'official_email' => 'snoop@omnicorp.internal',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->toDateString(),
        ]);

        $response = $this->actingAs($otherUser)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenant->id,
                'X-Employee-ID' => $otherEmp->id,
            ])
            ->getJson("/api/me/pay/payslips/{$payslipId}");

        $response->assertStatus(403);
    }
}
