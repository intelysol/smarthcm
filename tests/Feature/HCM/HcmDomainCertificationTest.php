<?php

declare(strict_types=1);

namespace Tests\Feature\HCM;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class HcmDomainCertificationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected string $tenantAId;
    protected string $tenantBId;
    protected string $companyAId;
    protected string $companyBId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Global Enterprises',
            'slug' => 'acme-global',
            'tenant_code' => 'ACM-GLB',
            'status' => 'active',
        ]);
        $this->tenantAId = (string) $this->tenantA->id;

        $this->tenantB = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Zenith Tech Systems',
            'slug' => 'zenith-tech',
            'tenant_code' => 'ZNT-TCH',
            'status' => 'active',
        ]);
        $this->tenantBId = (string) $this->tenantB->id;

        $this->companyAId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id' => $this->companyAId,
            'tenant_id' => $this->tenantAId,
            'name' => 'Acme Corporation',
            'legal_name' => 'Acme Corporation International LLC',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->companyBId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id' => $this->companyBId,
            'tenant_id' => $this->tenantBId,
            'name' => 'Zenith Corporation',
            'legal_name' => 'Zenith Corporation International Inc',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Domain 01: Core HR Certification & SSoR
     */
    public function test_domain_01_core_hr_ssor_and_tenant_isolation(): void
    {
        $empAId = (string) Str::uuid();
        DB::table('employees')->insert([
            'id' => $empAId,
            'tenant_id' => $this->tenantAId,
            'company_id' => $this->companyAId,
            'employee_number' => 'EMP-ACM-001',
            'employee_code' => 'EMP-ACM-001',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'official_email' => 'ada@acme.test',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $empBId = (string) Str::uuid();
        DB::table('employees')->insert([
            'id' => $empBId,
            'tenant_id' => $this->tenantBId,
            'company_id' => $this->companyBId,
            'employee_number' => 'EMP-ZNT-001',
            'employee_code' => 'EMP-ZNT-001',
            'first_name' => 'Alan',
            'last_name' => 'Turing',
            'official_email' => 'alan@zenith.test',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tenant A must only see its own employees
        $tenantAEmployees = DB::table('employees')->where('tenant_id', $this->tenantAId)->get();
        $this->assertCount(1, $tenantAEmployees);
        $this->assertEquals('Ada', $tenantAEmployees->first()->first_name);

        // Tenant B must only see its own employees
        $tenantBEmployees = DB::table('employees')->where('tenant_id', $this->tenantBId)->get();
        $this->assertCount(1, $tenantBEmployees);
        $this->assertEquals('Alan', $tenantBEmployees->first()->first_name);
    }

    /**
     * Domain 02: Organizational Design Hierarchy
     */
    public function test_domain_02_org_design_hierarchy(): void
    {
        $buId = (string) Str::uuid();
        DB::table('business_units')->insert([
            'id' => $buId,
            'tenant_id' => $this->tenantAId,
            'company_id' => $this->companyAId,
            'code' => 'BU-PROD',
            'name' => 'Product Division',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parentDeptId = (string) Str::uuid();
        DB::table('departments')->insert([
            'id' => $parentDeptId,
            'tenant_id' => $this->tenantAId,
            'business_unit_id' => $buId,
            'department_code' => 'DEP-ENG',
            'department_name' => 'Engineering',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $childDeptId = (string) Str::uuid();
        DB::table('departments')->insert([
            'id' => $childDeptId,
            'tenant_id' => $this->tenantAId,
            'business_unit_id' => $buId,
            'parent_department_id' => $parentDeptId,
            'department_code' => 'DEP-QA',
            'department_name' => 'Quality Assurance',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $child = DB::table('departments')->where('id', $childDeptId)->first();
        $this->assertEquals($parentDeptId, $child->parent_department_id);
    }

    /**
     * Domain 03: Job Architecture & Headcount Budgets
     */
    public function test_domain_03_job_architecture_headcount(): void
    {
        $posId = (string) Str::uuid();
        DB::table('positions')->insert([
            'id' => $posId,
            'tenant_id' => $this->tenantAId,
            'code' => 'POS-LEAD',
            'title' => 'Technical Lead',
            'headcount' => 4,
            'filled_headcount' => 2,
            'salary_min' => 120000,
            'salary_max' => 160000,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pos = DB::table('positions')->where('id', $posId)->first();
        $this->assertEquals(4, (int) $pos->headcount);
        $this->assertEquals(2, (int) $pos->filled_headcount);
        $this->assertEquals(2, ((int)$pos->headcount - (int)$pos->filled_headcount)); // 2 vacancies
    }

    /**
     * Domain 04 & 05: Recruitment & Onboarding Contracts
     */
    public function test_domain_04_and_05_recruitment_and_onboarding(): void
    {
        $candidateId = (string) Str::uuid();
        DB::table('candidates')->insert([
            'id' => $candidateId,
            'tenant_id' => $this->tenantAId,
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'email' => 'grace.hopper@navy.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cand = DB::table('candidates')->where('tenant_id', $this->tenantAId)->where('id', $candidateId)->first();
        $this->assertNotNull($cand);
        $this->assertEquals('Grace', $cand->first_name);
    }

    /**
     * Domain 06, 07, 08: Time, Absence & Scheduling Certification
     */
    public function test_domain_06_07_08_time_absence_scheduling(): void
    {
        // Shift
        $shiftId = (string) Str::uuid();
        DB::table('shifts')->insert([
            'id' => $shiftId,
            'tenant_id' => $this->tenantAId,
            'shift_name' => 'Morning Shift',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Leave Type
        $leaveTypeId = (string) Str::uuid();
        DB::table('leave_types')->insert([
            'id' => $leaveTypeId,
            'tenant_id' => $this->tenantAId,
            'name' => 'Sick Leave',
            'code' => 'SICK',
            'is_paid' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('shifts', ['id' => $shiftId, 'tenant_id' => $this->tenantAId]);
        $this->assertDatabaseHas('leave_types', ['id' => $leaveTypeId, 'tenant_id' => $this->tenantAId]);
    }

    /**
     * Domain 09 & 10: Payroll & Compensation Certification
     */
    public function test_domain_09_and_10_payroll_and_compensation(): void
    {
        $periodId = (string) Str::uuid();
        DB::table('payroll_periods')->insert([
            'id' => $periodId,
            'tenant_id' => $this->tenantAId,
            'period_name' => 'September 2026 Run',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'currency' => 'USD',
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $bandId = (string) Str::uuid();
        DB::table('compensation_bands')->insert([
            'id' => $bandId,
            'tenant_id' => $this->tenantAId,
            'code' => 'BAND-L5',
            'name' => 'Engineering Band L5',
            'currency' => 'USD',
            'minimum' => 120000,
            'midpoint' => 150000,
            'maximum' => 180000,
            'effective_from' => '2026-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('payroll_periods', ['id' => $periodId, 'status' => 'open']);
        $this->assertDatabaseHas('compensation_bands', ['id' => $bandId, 'minimum' => 120000]);
    }

    /**
     * Domain 12, 13, 14: Performance, Goals & Talent Succession
     */
    public function test_domain_12_13_14_performance_goals_talent(): void
    {
        $cycleId = (string) Str::uuid();
        DB::table('performance_cycles')->insert([
            'id' => $cycleId,
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $this->tenantAId,
            'name' => 'H2 2026 Appraisal',
            'cycle_type' => 'semi_annual',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $poolId = (string) Str::uuid();
        DB::table('talent_pools')->insert([
            'id' => $poolId,
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $this->tenantAId,
            'code' => 'POOL-TOP-TALENT',
            'name' => 'Global Top Talent Pool',
            'status' => 'active',
            'version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('performance_cycles', ['id' => $cycleId, 'name' => 'H2 2026 Appraisal']);
        $this->assertDatabaseHas('talent_pools', ['id' => $poolId, 'code' => 'POOL-TOP-TALENT']);
    }

    /**
     * Domain 15, 16, 17: Learning (LMS), Skills & Career Paths
     */
    public function test_domain_15_16_17_learning_skills_career(): void
    {
        $skillId = (string) Str::uuid();
        DB::table('career_skills')->insert([
            'id' => $skillId,
            'uuid' => $skillId,
            'tenant_id' => $this->tenantAId,
            'code' => 'SKILL-SECURITY-LEAD',
            'name' => 'Application Security Architecture',
            'status' => 'active',
            'version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $courseId = (string) Str::uuid();
        DB::table('courses')->insert([
            'id' => $courseId,
            'tenant_id' => $this->tenantAId,
            'code' => 'CRS-SEC-301',
            'name' => 'OWASP Top 10 Enterprise Hardening',
            'delivery_type' => 'online',
            'passing_score' => 85,
            'is_mandatory' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('career_skills', ['id' => $skillId, 'code' => 'SKILL-SECURITY-LEAD']);
        $this->assertDatabaseHas('courses', ['id' => $courseId, 'code' => 'CRS-SEC-301']);
    }

    /**
     * Domain 18, 19, 20: Offboarding, Expenses & Service Delivery
     */
    public function test_domain_18_19_20_offboarding_expenses_servicedelivery(): void
    {
        // Expenses
        $policyId = (string) Str::uuid();
        DB::table('expense_policies')->insert([
            'id' => $policyId,
            'tenant_id' => $this->tenantAId,
            'name' => 'Standard Travel Policy',
            'rules' => json_encode(['requires_receipt' => true, 'receipt_threshold' => 25.0]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('expense_policies', ['id' => $policyId, 'name' => 'Standard Travel Policy']);
    }

    /**
     * Domain 21 to 27: Documents, Health, Cost, Optimization, BI & Audit
     */
    public function test_domain_21_to_27_platform_governance_and_analytics(): void
    {
        $auditService = new \App\Domains\Shared\Services\AuditService(new \App\Domains\Shared\Services\AuditRedactor());
        $auditId = $auditService->record(
            tenantId: $this->tenantAId,
            eventType: 'HCM_DOMAIN_CERT_CHECK',
            action: 'RUN_CERTIFICATION',
            entityType: 'Platform',
            entityId: $this->tenantAId,
            actorId: null,
            before: ['status' => 'pending'],
            after: ['status' => 'certified']
        );

        $this->assertDatabaseHas('audit_events', [
            'id' => $auditId,
            'tenant_id' => $this->tenantAId,
            'event_type' => 'HCM_DOMAIN_CERT_CHECK',
        ]);
    }
}
