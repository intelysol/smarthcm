<?php

namespace Tests\Feature\Payroll;

use App\Domains\Attendance\Models\Timesheet;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Models\PayrollBonus;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Payroll\Services\PayrollInputService;
use App\Domains\Payroll\Services\PayrollPeriodService;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\PayrollDefaultDataSeeder;
use Database\Seeders\PayrollPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollInputCollectionAndAttendanceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PayrollPermissionSeeder::class);
    }

    public function test_input_collection_aggregates_approved_timesheets_and_bonuses(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);

        $seeder = new PayrollDefaultDataSeeder();
        $seeder->seedTenantData($tenant->id);

        $periodService = app(PayrollPeriodService::class);
        $period = $periodService->createPeriod($tenant->id, [
            'period_name' => 'September 2026 Regular',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]);

        // 1. Create Approved Timesheet (160 regular hours, 10 overtime hours)
        Timesheet::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'period_type' => 'monthly',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'total_scheduled_minutes' => 9600,
            'total_worked_minutes' => 10200,
            'total_regular_minutes' => 9600, // 160h
            'total_approved_overtime_minutes' => 600, // 10h
            'total_absence_minutes' => 0,
            'total_late_minutes' => 0,
            'total_early_departure_minutes' => 0,
            'status' => 'hr_approved',
        ]);

        // 2. Create Approved Performance Bonus ($1,000)
        PayrollBonus::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'bonus_type' => 'performance',
            'title' => 'Q3 Excellence Bonus',
            'amount' => 1000.00,
            'currency' => 'USD',
            'effective_date' => '2026-09-15',
            'status' => 'approved',
        ]);

        // 3. Collect Inputs
        $inputService = app(PayrollInputService::class);
        $input = $inputService->collectInputsForEmployee($period, $employee);

        $this->assertEquals('collected', $input->status);
        $this->assertCount(3, $input->lines); // Regular hours, Overtime hours, Bonus

        $otLine = $input->lines->firstWhere('input_type', 'overtime_hours');
        $this->assertNotNull($otLine);
        $this->assertEquals(10.0, (float) $otLine->quantity);

        $bonusLine = $input->lines->firstWhere('input_type', 'bonus');
        $this->assertNotNull($bonusLine);
        $this->assertEquals(1000.0, (float) $bonusLine->amount);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Bob',
            'last_name' => 'Miller',
            'official_email' => 'bob.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
