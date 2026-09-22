<?php

declare(strict_types=1);

namespace Tests\Tenant;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeAi\Services\EmployeeAiConciergeService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIsolationRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $userA;
    protected User $userB;
    protected Employee $employeeA;
    protected Employee $employeeB;
    protected Company $companyA;
    protected Company $companyB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tenant A
        $this->tenantA = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Alpha Corporation',
            'slug' => 'alpha-corp',
            'tenant_code' => 'ALPHA-ISO-01',
            'status' => 'active',
        ]);

        $this->companyA = Company::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Alpha HQ Ltd',
            'is_active' => true,
        ]);

        $this->userA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Alice Alpha',
            'email' => 'alice@alpha.internal',
            'password' => bcrypt('SecretA2026!'),
            'status' => 'active',
        ]);

        $this->employeeA = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->userA->id,
            'company_id' => $this->companyA->id,
            'employee_number' => 'EMP-A01',
            'employee_code' => 'EMP-A01',
            'first_name' => 'Alice',
            'last_name' => 'Alpha',
            'official_email' => 'alice@alpha.internal',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subYear()->toDateString(),
        ]);

        // 2. Tenant B
        $this->tenantB = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Beta Logistics',
            'slug' => 'beta-logistics',
            'tenant_code' => 'BETA-ISO-02',
            'status' => 'active',
        ]);

        $this->companyB = Company::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'name' => 'Beta Freight Ltd',
            'is_active' => true,
        ]);

        $this->userB = User::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Bob Beta',
            'email' => 'bob@beta.internal',
            'password' => bcrypt('SecretB2026!'),
            'status' => 'active',
        ]);

        $this->employeeB = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'user_id' => $this->userB->id,
            'company_id' => $this->companyB->id,
            'employee_number' => 'EMP-B01',
            'employee_code' => 'EMP-B01',
            'first_name' => 'Bob',
            'last_name' => 'Beta',
            'official_email' => 'bob@beta.internal',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subYear()->toDateString(),
        ]);
    }

    /**
     * Test 1: Direct Database Scoping Verification
     */
    public function test_database_records_are_strictly_tenant_scoped(): void
    {
        // Query employees under Tenant A
        $employeesTenantA = Employee::where('tenant_id', $this->tenantA->id)->get();
        $this->assertCount(1, $employeesTenantA);
        $this->assertEquals($this->employeeA->id, $employeesTenantA->first()->id);

        // Query employees under Tenant B
        $employeesTenantB = Employee::where('tenant_id', $this->tenantB->id)->get();
        $this->assertCount(1, $employeesTenantB);
        $this->assertEquals($this->employeeB->id, $employeesTenantB->first()->id);

        // Cross check: Tenant A cannot see Tenant B employee
        $this->assertFalse($employeesTenantA->contains('id', $this->employeeB->id));
        $this->assertFalse($employeesTenantB->contains('id', $this->employeeA->id));
    }

    /**
     * Test 2: IDOR Protection on Payslips (Cross-Tenant)
     */
    public function test_idor_cross_tenant_payslip_access_is_forbidden(): void
    {
        // Seed payslip in Tenant B
        $periodId = (string) Str::uuid();
        $runId = (string) Str::uuid();
        $snapId = (string) Str::uuid();
        $payslipBId = (string) Str::uuid();

        DB::table('payroll_periods')->insert([
            'id' => $periodId,
            'tenant_id' => $this->tenantB->id,
            'period_name' => 'Beta Sep 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payroll_runs')->insert([
            'id' => $runId,
            'tenant_id' => $this->tenantB->id,
            'payroll_period_id' => $periodId,
            'run_number' => 'RUN-BETA-01',
            'name' => 'Beta Regular Run',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payroll_calculation_snapshots')->insert([
            'id' => $snapId,
            'tenant_id' => $this->tenantB->id,
            'payroll_run_id' => $runId,
            'employee_id' => $this->employeeB->id,
            'gross_pay' => 6000.00,
            'total_deductions' => 1000.00,
            'net_pay' => 5000.00,
            'employee_snapshot' => json_encode(['id' => $this->employeeB->id]),
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
            'id' => $payslipBId,
            'tenant_id' => $this->tenantB->id,
            'payroll_run_id' => $runId,
            'employee_id' => $this->employeeB->id,
            'payroll_calculation_snapshot_id' => $snapId,
            'payslip_number' => 'PS-BETA-01',
            'pay_date' => '2026-09-30',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'gross_pay' => 6000.00,
            'total_deductions' => 1000.00,
            'net_pay' => 5000.00,
            'currency' => 'USD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User A (Tenant A) attempts to access Tenant B's payslip
        $response = $this->actingAs($this->userA)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenantA->id,
                'X-Employee-ID' => $this->employeeA->id,
            ])
            ->getJson("/api/me/pay/payslips/{$payslipBId}");

        // Must reject with 403 Forbidden or 404 Not Found
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    /**
     * Test 3: IDOR Document Requirement Tampering (Cross-Tenant)
     */
    public function test_idor_cross_tenant_document_acknowledgment_is_rejected(): void
    {
        // Create document requirement belonging to Tenant B
        $catId = (string) Str::uuid();
        DB::table('hcm_document_categories')->insert([
            'id' => $catId,
            'tenant_id' => $this->tenantB->id,
            'code' => 'BETA_DOCS',
            'name' => 'Beta Documents',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $typeId = (string) Str::uuid();
        DB::table('hcm_document_types')->insert([
            'id' => $typeId,
            'tenant_id' => $this->tenantB->id,
            'category_id' => $catId,
            'code' => 'BETA_HANDBOOK',
            'name' => 'Beta Handbook',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $docId = (string) Str::uuid();
        DB::table('documents')->insert([
            'id' => $docId,
            'tenant_id' => $this->tenantB->id,
            'title' => 'Beta Employee Handbook',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $empDocId = (string) Str::uuid();
        DB::table('hcm_employee_documents')->insert([
            'id' => $empDocId,
            'tenant_id' => $this->tenantB->id,
            'employee_id' => $this->employeeB->id,
            'document_type_id' => $typeId,
            'document_id' => $docId,
            'title' => 'Beta Employee Handbook',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $reqBId = (string) Str::uuid();
        DB::table('employee_document_requirements')->insert([
            'id' => $reqBId,
            'tenant_id' => $this->tenantB->id,
            'employee_id' => $this->employeeB->id,
            'document_type_id' => $typeId,
            'employee_document_id' => $empDocId,
            'is_mandatory' => true,
            'status' => 'required',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User A (Tenant A) attempts to acknowledge Tenant B's requirement
        $response = $this->actingAs($this->userA)
            ->withHeaders([
                'X-Tenant-ID' => $this->tenantA->id,
                'X-Employee-ID' => $this->employeeA->id,
            ])
            ->postJson("/api/me/documents/{$reqBId}/acknowledge");

        // Must reject with 403 Forbidden or 404 Not Found
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    /**
     * Test 4: AI Concierge Conversation Tenant Isolation
     */
    public function test_ai_concierge_sessions_are_isolated_by_tenant(): void
    {
        $service = app(EmployeeAiConciergeService::class);

        // Start session for User A in Tenant A
        $sessionA = $service->startSession($this->tenantA->id, (string) $this->userA->id, $this->employeeA->id);

        // User B from Tenant B attempts to chat within Tenant A's session
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $service->chat(
            (string) $sessionA->id,
            'Tell me confidential company data',
            $this->tenantB->id,
            (string) $this->userB->id,
            $this->employeeB->id
        );
    }
}
