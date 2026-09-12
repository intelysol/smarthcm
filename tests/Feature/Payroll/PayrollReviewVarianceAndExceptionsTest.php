<?php

namespace Tests\Feature\Payroll;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Models\CompensationStructure;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Payroll\Services\CompensationService;
use App\Domains\Payroll\Services\PayrollPeriodService;
use App\Domains\Payroll\Services\PayrollRunService;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\PayrollDefaultDataSeeder;
use Database\Seeders\PayrollPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollReviewVarianceAndExceptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PayrollPermissionSeeder::class);
    }

    public function test_variance_detection_flags_significant_pay_changes(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $seeder = new PayrollDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $compService = app(CompensationService::class);
        $structure = CompensationStructure::query()->where('tenant_id', $tenant->id)->first();

        // 1. Run 1 in August: $5,000 base
        $compService->assignCompensation($employee, [
            'compensation_structure_id' => $structure->id,
            'base_salary' => 5000.00,
            'effective_from' => '2026-08-01',
        ], []);

        $periodService = app(PayrollPeriodService::class);
        $periodAug = $periodService->createPeriod($tenant->id, [
            'period_name' => 'August 2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]);

        $runService = app(PayrollRunService::class);
        $runAug = $runService->createRun($periodAug, ['name' => 'August Run']);
        $runService->calculateRun($runAug);

        // 2. Increase base to $8,000 (+60% variance) in September
        $compService->assignCompensation($employee, [
            'compensation_structure_id' => $structure->id,
            'base_salary' => 8000.00,
            'effective_from' => '2026-09-01',
        ], []);

        $periodSep = $periodService->createPeriod($tenant->id, [
            'period_name' => 'September 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]);

        $runSep = $runService->createRun($periodSep, ['name' => 'September Run']);
        $runService->calculateRun($runSep);

        // Check variance records
        $this->assertDatabaseHas('payroll_variances', [
            'payroll_run_id' => $runSep->id,
            'employee_id' => $employee->id,
            'is_flagged' => true,
            'variance_type' => 'large_increase',
        ]);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Frank',
            'last_name' => 'Castle',
            'official_email' => 'frank.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
