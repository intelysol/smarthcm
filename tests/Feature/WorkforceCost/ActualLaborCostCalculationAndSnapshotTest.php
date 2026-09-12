<?php

namespace Tests\Feature\WorkforceCost;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Payroll\Models\PayrollCalculationSnapshot;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Models\PayrollRun;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceCost\Services\LaborCostAggregationService;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActualLaborCostCalculationAndSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_actual_payroll_cost_ingestion_and_snapshot_generation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Operations BU',
            'code' => 'OBU-' . Str::random(4),
        ]);
        $department = Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_name' => 'Finance',
            'department_code' => 'FIN-' . Str::random(4),
        ]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $department->id,
            'employee_code' => 'EMP-CST-01',
            'employee_number' => '300101',
            'first_name' => 'Tariq',
            'last_name' => 'Mehmood',
            'official_email' => 'tariq.m@example.com',
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
            'run_number' => 'PR-2026-10-01',
            'name' => 'October 2026 Regular Payroll',
            'run_type' => 'regular',
            'currency' => 'USD',
            'status' => 'approved',
            'employee_count' => 1,
            'gross_total' => 5000.00,
            'earnings_total' => 5000.00,
            'deduction_total' => 500.00,
            'tax_total' => 500.00,
            'employer_cost_total' => 1250.00,
            'net_total' => 4000.00,
            'calculated_at' => now(),
            'approved_at' => now(),
        ]);

        PayrollCalculationSnapshot::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'payroll_run_id' => $payrollRun->id,
            'employee_id' => $employee->id,
            'gross_pay' => 5000.00,
            'total_deductions' => 500.00,
            'total_tax' => 500.00,
            'total_employer_cost' => 1250.00,
            'net_pay' => 4000.00,
            'currency' => 'USD',
            'employee_snapshot' => ['name' => 'Tariq Mehmood'],
            'compensation_snapshot' => ['base' => 5000],
            'inputs_snapshot' => [],
            'earnings_snapshot' => [['code' => 'BASIC', 'amount' => 5000]],
            'deductions_snapshot' => [],
            'taxes_snapshot' => [],
            'employer_contributions_snapshot' => [['code' => 'TAX', 'amount' => 1250]],
            'idempotency_key' => (string) Str::uuid(),
            'calculated_at' => now(),
        ]);

        $service = app(LaborCostAggregationService::class);

        // 1. Ingest Payroll Run
        $linesCount = $service->ingestPayrollRun($tenant->id, $payrollRun->id);
        $this->assertEquals(2, $linesCount); // 1 base pay + 1 employer burden

        $this->assertDatabaseHas('hcm_workforce_cost_lines', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'component_type' => 'BASE_PAY',
            'cost_category' => 'direct_labor',
            'amount' => 5000.00,
        ]);

        $this->assertDatabaseHas('hcm_workforce_cost_lines', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'component_type' => 'EMPLOYER_TAX',
            'cost_category' => 'burden',
            'amount' => 1250.00,
        ]);

        // 2. Build Snapshot
        $snapshot = $service->buildSnapshot(
            $tenant->id,
            '2026-10',
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-10-31')
        );

        $this->assertNotNull($snapshot);
        $this->assertEquals(6250.00, (float) $snapshot->total_workforce_cost);
        $this->assertEquals(5000.00, (float) $snapshot->total_direct_labor);
        $this->assertEquals(1250.00, (float) $snapshot->total_burden);
        $this->assertEquals(1, $snapshot->total_headcount);
        $this->assertEquals(1.00, (float) $snapshot->total_fte);

        // 3. API Snapshot Verification
        $response = $this->actingAs($user)->getJson("/api/v1/hcm/workforce-cost/snapshots/{$snapshot->id}?tenant_id={$tenant->id}");
        $response->assertStatus(200);
        $response->assertJsonPath('data.total_workforce_cost', '6250.0000');
    }
}