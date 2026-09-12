<?php

namespace Tests\Feature\WorkforceProductivity;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceProductivity\Services\ProductivityMeasurementService;
use App\Domains\WorkforceProductivity\Services\ProductivityUtilizationService;
use App\Domains\WorkforceProductivity\Services\ScheduleEffectivenessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceUtilizationAndScheduleEffectivenessTest extends TestCase
{
    use RefreshDatabase;

    protected ProductivityMeasurementService $measurementService;
    protected ProductivityUtilizationService $utilizationService;
    protected ScheduleEffectivenessService $scheduleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->measurementService = app(ProductivityMeasurementService::class);
        $this->utilizationService = app(ProductivityUtilizationService::class);
        $this->scheduleService = app(ScheduleEffectivenessService::class);
    }

    public function test_utilization_calculation_and_non_productive_breakdown(): void
    {
        $tenant = Tenant::factory()->create();

        // Record productive and non-productive hours for 2026-10
        $this->measurementService->recordTime([
            'tenant_id' => $tenant->id,
            'record_date' => '2026-10-10',
            'category' => 'PRODUCTIVE',
            'hours' => 80.0,
        ]);
        $this->measurementService->recordTime([
            'tenant_id' => $tenant->id,
            'record_date' => '2026-10-11',
            'category' => 'TRAINING',
            'hours' => 10.0,
        ]);
        $this->measurementService->recordTime([
            'tenant_id' => $tenant->id,
            'record_date' => '2026-10-12',
            'category' => 'WAITING',
            'hours' => 20.0, // 20 / (80+10+20) = 18.1% > 15% bottleneck threshold
        ]);

        $result = $this->utilizationService->calculateUtilization(
            tenantId: $tenant->id,
            departmentId: null,
            startDate: '2026-10-01',
            endDate: '2026-10-31',
            availableCapacityHours: 100.0,
            scheduledHours: 110.0
        );

        $this->assertEquals(80.0, $result->productiveHours);
        $this->assertEquals(80.0, $result->utilizationRate); // 80 / 100 * 100
        $this->assertEquals(110.0, $result->actualAttendedHours); // 80 + 10 + 20
        $this->assertArrayHasKey('WAITING', $result->nonProductiveBreakdown);
        $this->assertEquals(20.0, $result->nonProductiveBreakdown['WAITING']);

        // Verify that elevated waiting time was flagged as an operational bottleneck factor
        $this->assertNotEmpty($result->bottleneckFactors);
        $this->assertStringContainsString('waiting time', strtolower($result->bottleneckFactors[0]));
    }

    public function test_schedule_effectiveness_compares_scheduled_attended_productive_and_required(): void
    {
        $evaluation = $this->scheduleService->evaluateSchedule(
            scheduledHours: 1000.0,
            actualAttendedHours: 980.0,
            productiveHours: 850.0,
            requiredCapacityHours: 1050.0
        );

        $this->assertEquals(98.0, $evaluation['schedule_adherence_pct']); // 980 / 1000 * 100
        $this->assertEquals(86.73, $evaluation['productive_utilization_pct']); // 850 / 980 * 100
        $this->assertEquals(95.24, $evaluation['schedule_coverage_pct']); // 1000 / 1050 * 100
        $this->assertEquals(130.0, $evaluation['unutilized_hours']); // 980 - 850
        $this->assertTrue($evaluation['understaffed']); // required (1050) > scheduled (1000)
    }
}
