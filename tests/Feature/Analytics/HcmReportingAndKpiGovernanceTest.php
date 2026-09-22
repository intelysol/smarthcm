<?php

declare(strict_types=1);

namespace Tests\Feature\Analytics;

use App\Domains\Analytics\Models\HcmAnalyticsMetric;
use App\Domains\Analytics\Services\HcmCompensationAndPayrollAnalyticsService;
use App\Domains\Analytics\Services\HcmMetricRegistryService;
use App\Domains\Analytics\Services\HcmReportBuilderService;
use App\Domains\Analytics\Services\HcmWorkforceAnalyticsService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Payroll\Models\EmployeeCompensation;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class HcmReportingAndKpiGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected Company $companyA;
    protected Company $companyB;
    protected BusinessUnit $buA;
    protected Department $deptEngineering;
    protected Department $deptSales;
    protected Branch $branchHQ;
    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::factory()->create(['status' => 'active']);
        $this->tenantB = Tenant::factory()->create(['status' => 'active']);

        $this->companyA = Company::factory()->create(['tenant_id' => $this->tenantA->id]);
        $this->companyB = Company::factory()->create(['tenant_id' => $this->tenantB->id]);

        $this->buA = BusinessUnit::create([
            'tenant_id' => $this->tenantA->id,
            'company_id' => $this->companyA->id,
            'name' => 'HQ Unit',
            'code' => 'BU-01',
        ]);

        $this->deptEngineering = Department::create([
            'tenant_id' => $this->tenantA->id,
            'company_id' => $this->companyA->id,
            'business_unit_id' => $this->buA->id,
            'department_name' => 'Engineering',
            'department_code' => 'ENG',
        ]);

        $this->deptSales = Department::create([
            'tenant_id' => $this->tenantA->id,
            'company_id' => $this->companyA->id,
            'business_unit_id' => $this->buA->id,
            'department_name' => 'Sales',
            'department_code' => 'SLS',
        ]);

        $this->branchHQ = Branch::create([
            'tenant_id' => $this->tenantA->id,
            'company_id' => $this->companyA->id,
            'branch_name' => 'HQ Main',
            'branch_code' => 'BR-01',
        ]);

        $this->adminUser = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'is_platform_admin' => true,
            'status' => 'active',
        ]);

        $this->regularUser = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'is_platform_admin' => false,
            'status' => 'active',
        ]);
    }

    /**
     * 1. Authoritative KPI Registry & Version Lineage Resolution
     */
    public function test_kpi_registry_and_historical_version_resolution(): void
    {
        $registryService = app(HcmMetricRegistryService::class);

        // Register new governed KPI (v1)
        $metric = $registryService->createMetric($this->tenantA->id, [
            'code' => 'KPI_WORKFORCE_TURNOVER',
            'name' => 'Annualized Workforce Turnover Rate',
            'category' => 'turnover',
            'unit' => 'percentage',
            'aggregation' => 'avg',
            'formula' => '(Total Exits / Average Headcount) * 100',
            'effective_from' => '2025-01-01',
        ], $this->adminUser);

        $this->assertEquals('KPI_WORKFORCE_TURNOVER', $metric->code);
        $this->assertEquals(1, $metric->current_version);
        $this->assertTrue($metric->is_active);

        // Publish Version 2 with refined formula
        $v2 = $registryService->createNewVersion(
            $metric,
            [
                'formula' => '(Total Exits / ((Opening_HC + Closing_HC) / 2)) * 100',
                'aggregation' => 'avg',
            ],
            '2026-01-01',
            'Updated formula to use arithmetic mean of opening and closing headcount',
            $this->adminUser
        );

        $this->assertEquals(2, $v2->version_number);
        $metric->refresh();
        $this->assertEquals(2, $metric->current_version);

        // Historical resolution: 2025 gets v1, 2026 gets v2
        $ver2025 = $registryService->getVersionForDate($metric, '2025-06-01');
        $this->assertEquals(1, $ver2025->version_number);
        $this->assertStringContainsString('Average Headcount', $ver2025->formula);

        $ver2026 = $registryService->getVersionForDate($metric, '2026-06-01');
        $this->assertEquals(2, $ver2026->version_number);
        $this->assertStringContainsString('Opening_HC', $ver2026->formula);

        // Full metadata explanation
        $explanation = $registryService->getKpiExplanation($this->tenantA->id, 'KPI_WORKFORCE_TURNOVER', '2026-06-01');
        $this->assertEquals('KPI_WORKFORCE_TURNOVER', $explanation['kpi_code']);
        $this->assertEquals(2, $explanation['version']);
        $this->assertEquals('CERTIFIED', $explanation['certification_status']);
        $this->assertNotEmpty($explanation['lineage']['data_sources']);
    }

    /**
     * 2. Operational Reports: Employee Directory, Leave Requests, and Attendance
     */
    public function test_operational_report_execution_and_filtering(): void
    {
        $reportService = app(HcmReportBuilderService::class);

        // Seed employees in Tenant A
        $emp1 = Employee::create([
            'tenant_id' => $this->tenantA->id,
            'company_id' => $this->companyA->id,
            'department_id' => $this->deptEngineering->id,
            'branch_id' => $this->branchHQ->id,
            'employee_code' => 'EMP-TEST-01',
            'employee_number' => 'EMP-TEST-01',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'official_email' => 'alice@test.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-10',
        ]);

        $emp2 = Employee::create([
            'tenant_id' => $this->tenantA->id,
            'company_id' => $this->companyA->id,
            'department_id' => $this->deptSales->id,
            'branch_id' => $this->branchHQ->id,
            'employee_code' => 'EMP-TEST-02',
            'employee_number' => 'EMP-TEST-02',
            'first_name' => 'Bob',
            'last_name' => 'Jones',
            'official_email' => 'bob@test.com',
            'employment_status' => 'active',
            'joining_date' => '2025-03-15',
        ]);

        // A. Employee Directory Report (Raw records)
        $directoryReport = $reportService->executeReportQuery($this->tenantA->id, 'employee_directory', [
            'raw_records' => true,
            'filters' => ['department_id' => $this->deptEngineering->id],
        ], $this->adminUser);

        $this->assertEquals('employee_directory', $directoryReport['dataset']);
        $this->assertCount(1, $directoryReport['rows']);
        $this->assertEquals('Alice Smith', $directoryReport['rows'][0]['full_name']);

        // B. Leave Requests Report
        $leaveTypeId = (string) Str::uuid();
        DB::table('leave_types')->insert([
            'id' => $leaveTypeId,
            'tenant_id' => $this->tenantA->id,
            'name' => 'Annual Leave',
            'code' => 'AL-01',
            'is_paid' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $leaveAppId = (string) Str::uuid();
        DB::table('leave_applications')->insert([
            'id' => $leaveAppId,
            'tenant_id' => $this->tenantA->id,
            'employee_id' => $emp1->id,
            'leave_type_id' => $leaveTypeId,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-05',
            'duration' => 5.0,
            'unit' => 'days',
            'status' => 'approved',
            'reason' => 'Summer vacation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $leaveReport = $reportService->executeReportQuery($this->tenantA->id, 'leave_requests', [
            'filters' => ['status' => 'approved'],
        ], $this->adminUser);

        $this->assertGreaterThanOrEqual(1, $leaveReport['total_records_analyzed']);
        $this->assertEquals('approved', $leaveReport['rows'][0]['status']);
        $this->assertEquals(5.0, $leaveReport['rows'][0]['duration']);
    }

    /**
     * 3. Management Reports: Turnover and Headcount Analytics
     */
    public function test_management_report_turnover_and_headcount(): void
    {
        $workforceService = app(HcmWorkforceAnalyticsService::class);

        Employee::create([
            'tenant_id' => $this->tenantA->id,
            'company_id' => $this->companyA->id,
            'department_id' => $this->deptEngineering->id,
            'branch_id' => $this->branchHQ->id,
            'employee_code' => 'EMP-MGT-01',
            'employee_number' => 'EMP-MGT-01',
            'first_name' => 'Carol',
            'last_name' => 'Taylor',
            'official_email' => 'carol@test.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        Employee::create([
            'tenant_id' => $this->tenantA->id,
            'company_id' => $this->companyA->id,
            'department_id' => $this->deptSales->id,
            'branch_id' => $this->branchHQ->id,
            'employee_code' => 'EMP-MGT-02',
            'employee_number' => 'EMP-MGT-02',
            'first_name' => 'Dan',
            'last_name' => 'Miller',
            'official_email' => 'dan@test.com',
            'employment_status' => 'terminated',
            'joining_date' => '2025-01-01',
            'termination_date' => '2026-03-01',
            'exit_type' => 'voluntary',
        ]);

        $summary = $workforceService->getHeadcountSummary($this->tenantA->id, '2026-06-01');
        $this->assertEquals(1, $summary['active_headcount']);

        $turnover = $workforceService->getTurnoverAnalytics($this->tenantA->id, '2026-01-01', '2026-06-01');
        $this->assertEquals(1, $turnover['total_exits']);
        $this->assertEquals(1, $turnover['voluntary_exits']);
        $this->assertGreaterThan(0, $turnover['turnover_rate_percent']);
    }

    /**
     * 4. Compliance Report Execution (Certifications / Course Completions)
     */
    public function test_compliance_report_execution(): void
    {
        $reportService = app(HcmReportBuilderService::class);

        $result = $reportService->executeReportQuery($this->tenantA->id, 'certifications', [], $this->adminUser);
        $this->assertEquals('certifications', $result['dataset']);
        $this->assertIsArray($result['rows']);
    }

    /**
     * 5. Strict Multi-Tenant Analytical Isolation
     */
    public function test_strict_multi_tenant_analytical_isolation(): void
    {
        $reportService = app(HcmReportBuilderService::class);

        // Create Tenant A Employee
        Employee::create([
            'tenant_id' => $this->tenantA->id,
            'company_id' => $this->companyA->id,
            'employee_code' => 'EMP-TENA-1',
            'employee_number' => 'EMP-TENA-1',
            'first_name' => 'TenantA',
            'last_name' => 'User',
            'official_email' => 'a@tenant-a.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        // Create Tenant B Employee
        Employee::create([
            'tenant_id' => $this->tenantB->id,
            'company_id' => $this->companyB->id,
            'employee_code' => 'EMP-TENB-1',
            'employee_number' => 'EMP-TENB-1',
            'first_name' => 'TenantB',
            'last_name' => 'User',
            'official_email' => 'b@tenant-b.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        // Query Tenant A
        $resultA = $reportService->executeReportQuery($this->tenantA->id, 'employee_directory', ['raw_records' => true]);
        $emailsA = array_column($resultA['rows'], 'official_email');
        $this->assertContains('a@tenant-a.com', $emailsA);
        $this->assertNotContains('b@tenant-b.com', $emailsA);

        // Query Tenant B
        $resultB = $reportService->executeReportQuery($this->tenantB->id, 'employee_directory', ['raw_records' => true]);
        $emailsB = array_column($resultB['rows'], 'official_email');
        $this->assertContains('b@tenant-b.com', $emailsB);
        $this->assertNotContains('a@tenant-a.com', $emailsB);
    }

    /**
     * 6. Sensitive Report RBAC Permission Gating
     */
    public function test_sensitive_report_rbac_permission_gating(): void
    {
        $reportService = app(HcmReportBuilderService::class);

        // 1. Regular user without payroll.view permission is blocked
        $this->expectException(AuthorizationException::class);
        $reportService->executeReportQuery($this->tenantA->id, 'payroll_register', [], $this->regularUser);
    }

    public function test_sensitive_report_accessible_by_authorized_admin(): void
    {
        $reportService = app(HcmReportBuilderService::class);

        // 2. Admin user executes successfully
        $result = $reportService->executeReportQuery($this->tenantA->id, 'payroll_register', [], $this->adminUser);
        $this->assertEquals('payroll_register', $result['dataset']);
        $this->assertIsArray($result['rows']);
    }

    /**
     * 7. Scheduled Report Delivery Security Gate (Just-In-Time)
     */
    public function test_scheduled_report_delivery_security_gate(): void
    {
        $reportService = app(HcmReportBuilderService::class);

        // A. Valid delivery for admin user
        $gateAdmin = $reportService->validateScheduledDelivery($this->tenantA->id, $this->adminUser->id, 'REP-MGT-003');
        $this->assertTrue($gateAdmin['authorized']);

        // B. Denied delivery for regular user on sensitive report
        $gateRegular = $reportService->validateScheduledDelivery($this->tenantA->id, $this->regularUser->id, 'REP-MGT-003');
        $this->assertFalse($gateRegular['authorized']);
        $this->assertStringContainsString('payroll.view', $gateRegular['reason']);

        // C. Denied delivery for inactive user
        $inactiveUser = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'status' => 'inactive',
        ]);
        $gateInactive = $reportService->validateScheduledDelivery($this->tenantA->id, $inactiveUser->id, 'REP-OPS-001');
        $this->assertFalse($gateInactive['authorized']);
        $this->assertStringContainsString('inactive', $gateInactive['reason']);

        // D. Denied delivery for suspended tenant
        $suspendedTenant = Tenant::factory()->create(['status' => 'suspended']);
        $gateSuspended = $reportService->validateScheduledDelivery($suspendedTenant->id, $this->adminUser->id, 'REP-OPS-001');
        $this->assertFalse($gateSuspended['authorized']);
        $this->assertStringContainsString('suspended', $gateSuspended['reason']);
    }

    /**
     * 8. CSV Export Formula Injection Neutralization
     */
    public function test_report_csv_export_sanitization(): void
    {
        $reportService = app(HcmReportBuilderService::class);

        $maliciousReport = [
            'dataset' => 'test_export',
            'rows' => [
                [
                    'name' => '=SUM(A1:A10)',
                    'command' => '+cmd|/C calc!A0',
                    'formula' => '-5*2',
                    'macro' => '@SUM(1+1)',
                    'safe_col' => 'Normal Text',
                ],
            ],
        ];

        $csv = $reportService->generateCsvExport($maliciousReport);

        // Assert all formula payloads are neutralized with a leading single quote
        $this->assertStringContainsString("'=SUM(A1:A10)", $csv);
        $this->assertStringContainsString("'+cmd|/C calc!A0", $csv);
        $this->assertStringContainsString("'-5*2", $csv);
        $this->assertStringContainsString("'@SUM(1+1)", $csv);
        $this->assertStringContainsString("Normal Text", $csv);
    }

    /**
     * 9. Executive Dashboard Live Calculations & Zero Mock Placeholders
     */
    public function test_executive_dashboard_real_data_no_mock(): void
    {
        // Seed active employee and compensation in Tenant A
        $emp = Employee::create([
            'tenant_id' => $this->tenantA->id,
            'company_id' => $this->companyA->id,
            'department_id' => $this->deptEngineering->id,
            'branch_id' => $this->branchHQ->id,
            'employee_code' => 'EMP-EXEC-01',
            'employee_number' => 'EMP-EXEC-01',
            'first_name' => 'Exec',
            'last_name' => 'Officer',
            'official_email' => 'exec@company.com',
            'employment_status' => 'active',
            'joining_date' => now()->subYears(2)->toDateString(),
        ]);

        EmployeeCompensation::create([
            'tenant_id' => $this->tenantA->id,
            'employee_id' => $emp->id,
            'base_salary' => 12500.00,
            'currency' => 'USD',
            'pay_frequency' => 'monthly',
            'effective_from' => '2025-01-01',
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser);
        session(['tenant_uuid' => $this->tenantA->id]);

        // GET Executive Overview
        $responseOverview = $this->get(route('executive.overview'));
        $responseOverview->assertStatus(200);

        $kpis = $responseOverview->viewData('kpis');
        $this->assertNotNull($kpis);
        $this->assertEquals(1, $kpis['total_headcount']);
        $this->assertEquals('100.0%', $kpis['retention_rate']);
        $this->assertEquals('0.0%', $kpis['annual_turnover']);
        $this->assertStringContainsString('$12,500.00', $kpis['payroll_cost_runrate']);
        // Verify mock placeholder string '94.8%' is not present
        $this->assertNotEquals('94.8%', $kpis['retention_rate']);

        // GET Executive Costs
        $responseCosts = $this->get(route('executive.costs'));
        $responseCosts->assertStatus(200);

        $costKpis = $responseCosts->viewData('costKpis');
        $this->assertNotNull($costKpis);
        $this->assertStringContainsString('$12,500.00', $costKpis['total_compensation_runrate']);
        // Verify mock placeholder string '$1,420,000' is not present
        $this->assertNotEquals('$1,420,000', $costKpis['total_compensation_runrate']);
    }
}
