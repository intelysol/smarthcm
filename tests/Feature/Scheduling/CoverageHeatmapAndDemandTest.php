<?php

namespace Tests\Feature\Scheduling;

use App\Domains\Attendance\Models\RosterAssignment;
use App\Domains\Attendance\Models\RosterPeriod;
use App\Domains\Attendance\Models\ShiftDefinition;
use App\Domains\Attendance\Services\Scheduling\CoverageCalculationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\AttendancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoverageHeatmapAndDemandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendancePermissionSeeder::class);
    }

    public function test_coverage_requirements_ingestion_and_heatmap_matrix(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $period = RosterPeriod::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Support Team Coverage Q4',
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-07',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $shiftMorning = ShiftDefinition::query()->create([
            'tenant_id' => $tenant->id,
            'shift_code' => 'MORN',
            'name' => 'Morning Shift',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'duration_minutes' => 480,
            'is_active' => true,
        ]);

        $coverageService = app(CoverageCalculationService::class);

        // 1. Ingest Demand Requirements (simulating Epic 2.45 capacity feed)
        $requirements = [
            [
                'date' => '2026-10-05',
                'shift_definition_id' => $shiftMorning->id,
                'required_headcount' => 3,
            ],
            [
                'date' => '2026-10-06',
                'shift_definition_id' => $shiftMorning->id,
                'required_headcount' => 2,
            ],
        ];

        $ingested = $coverageService->ingestRequirements($period, $requirements);
        $this->assertCount(2, $ingested);
        $this->assertDatabaseHas('hcm_schedule_coverage_requirements', [
            'roster_period_id' => $period->id,
            'required_headcount' => 3,
        ]);

        // 2. Assign 2 employees on 2026-10-05 (Deficit of 1)
        $emp1 = $this->createEmployee($tenant->id, $company->id);
        $emp2 = $this->createEmployee($tenant->id, $company->id);

        RosterAssignment::query()->create([
            'tenant_id' => $tenant->id,
            'roster_period_id' => $period->id,
            'employee_id' => $emp1->id,
            'roster_date' => '2026-10-05',
            'shift_definition_id' => $shiftMorning->id,
            'assignment_status' => 'scheduled',
        ]);
        RosterAssignment::query()->create([
            'tenant_id' => $tenant->id,
            'roster_period_id' => $period->id,
            'employee_id' => $emp2->id,
            'roster_date' => '2026-10-05',
            'shift_definition_id' => $shiftMorning->id,
            'assignment_status' => 'scheduled',
        ]);

        // 3. Compute Matrix & Heatmap
        $result = $coverageService->getCoverageMatrix($period);

        $this->assertEquals(5, $result['overall_required']); // 3 + 2
        $this->assertEquals(2, $result['overall_scheduled']);
        $this->assertEquals(-3, $result['overall_gap']);
        $this->assertEquals(40.0, $result['overall_coverage_pct']);

        $heatmapRow1 = collect($result['heatmap'])->firstWhere('date', '2026-10-05');
        $this->assertEquals(3, $heatmapRow1['required']);
        $this->assertEquals(2, $heatmapRow1['scheduled']);
        $this->assertEquals(-1, $heatmapRow1['gap']);
        $this->assertEquals('under_covered', $heatmapRow1['status']);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Usman',
            'last_name' => 'Khan',
            'official_email' => 'usman.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
