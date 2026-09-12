<?php

namespace Tests\Feature\WorkforceCost;

use App\Domains\Attendance\Models\HcmOvertimeTierRecord;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceCost\Services\LaborCostAggregationService;
use App\Domains\WorkforceCost\Services\WorkforceEconomicsService;
use Carbon\Carbon;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OvertimeAndAttendanceCostEconomicsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_overtime_ingestion_and_labor_cost_economics(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-OT-01',
            'employee_number' => '300201',
            'first_name' => 'Bilal',
            'last_name' => 'Hassan',
            'official_email' => 'bilal.h@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $aggService = app(LaborCostAggregationService::class);

        // Base salary line: 4000, 160 hours
        $aggService->createCostLine($tenant->id, [
            'employee_id' => $employee->id,
            'cost_category' => 'direct_labor',
            'cost_nature' => 'ACTUAL',
            'component_type' => 'BASE_PAY',
            'cost_date' => '2026-10-15',
            'amount' => 4000.00,
            'hours_worked' => 160.00,
            'rate_per_hour' => 25.00,
        ]);

        // Overtime record: 600 minutes = 10 hours Tier 1 @ 1.5x ($37.50/hr) = $375
        HcmOvertimeTierRecord::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'overtime_date' => '2026-10-16',
            'tier_1_minutes' => 600,
            'total_overtime_minutes' => 600,
            'is_unauthorized' => false,
            'status' => 'approved',
        ]);

        $ingestedOt = $aggService->ingestAttendanceOvertime($tenant->id, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));
        $this->assertEquals(1, $ingestedOt);

        // Calculate Workforce Economics
        $econService = app(WorkforceEconomicsService::class);
        $economics = $econService->calculateEconomics($tenant->id, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));

        $this->assertNotNull($economics);
        // Total cost = 4000 + 375 = 4375. Total hours = 160 + 10 = 170.
        // Cost per labor hour = 4375 / 170 = 25.7353
        $this->assertEquals(25.7353, (float) $economics->cost_per_labor_hour);
        // Overtime ratio = 375 / 4375 * 100 = 8.57%
        $this->assertEquals(8.57, (float) $economics->overtime_cost_ratio);
    }
}