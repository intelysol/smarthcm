<?php

namespace Tests\Feature\WorkforceCost;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Models\PayrollCalculationSnapshot;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceCost\Models\HcmWorkforceCostLine;
use App\Domains\WorkforceCost\Services\LaborCostAggregationService;
use App\Domains\WorkforceCost\Services\WorkforceCostReconciliationService;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MultiWayCostReconciliationAndIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_payroll_reconciliation_and_idempotency_prevents_duplicate_cost(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-REC-01',
            'employee_number' => '300401',
            'first_name' => 'Adnan',
            'last_name' => 'Akmal',
            'official_email' => 'adnan.a@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $period = PayrollPeriod::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'period_name' => '2026-10',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'status' => 'opened',
        ]);

        $payrollRun = PayrollRun::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'payroll_period_id' => $period->id,
            'run_number' => 'PR-2026-10-REC',
            'name' => 'October Payroll Reconciliation Test',
            'gross_total' => 8000.00,
            'employer_cost_total' => 2000.00,
            'status' => 'approved',
        ]);

        PayrollCalculationSnapshot::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'payroll_run_id' => $payrollRun->id,
            'employee_id' => $employee->id,
            'gross_pay' => 8000.00,
            'total_employer_cost' => 2000.00,
            'total_deductions' => 0,
            'total_tax' => 0,
            'net_pay' => 8000.00,
            'employee_snapshot' => ['name' => 'Adnan Akmal'],
            'compensation_snapshot' => ['base' => 8000],
            'inputs_snapshot' => [],
            'earnings_snapshot' => [['code' => 'BASIC', 'amount' => 8000]],
            'deductions_snapshot' => [],
            'taxes_snapshot' => [],
            'employer_contributions_snapshot' => [['code' => 'TAX', 'amount' => 2000]],
            'idempotency_key' => (string) Str::uuid(),
        ]);

        $aggService = app(LaborCostAggregationService::class);
        $recService = app(WorkforceCostReconciliationService::class);

        // First ingestion: 2 lines
        $count1 = $aggService->ingestPayrollRun($tenant->id, $payrollRun->id);
        $this->assertEquals(2, $count1);

        // Second ingestion (idempotent test): must not duplicate lines
        $count2 = $aggService->ingestPayrollRun($tenant->id, $payrollRun->id);
        $this->assertEquals(2, $count2);

        $totalLines = HcmWorkforceCostLine::where('tenant_id', $tenant->id)->count();
        $this->assertEquals(2, $totalLines);

        // Run Reconciliation: Payroll total = 8000 + 2000 = 10000. Cost lines sum = 10000. Status = MATCHED.
        $reconciliation = $recService->reconcileWithPayroll($tenant->id, $payrollRun->id);
        $this->assertEquals('MATCHED', $reconciliation->status);
        $this->assertEquals(0.00, (float) $reconciliation->variance_amount);
    }
}