<?php

namespace Tests\Feature\WorkforceProductivity;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceProductivity\DTOs\ProductivityMeasurementData;
use App\Domains\WorkforceProductivity\Models\HcmProductivityMeasurement;
use App\Domains\WorkforceProductivity\Services\ProductivityMeasurementService;
use App\Domains\WorkforceProductivity\Services\ProductivityMetricService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductivityMeasurementAndProvenanceTest extends TestCase
{
    use RefreshDatabase;

    protected ProductivityMeasurementService $measurementService;
    protected ProductivityMetricService $metricService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->measurementService = app(ProductivityMeasurementService::class);
        $this->metricService = app(ProductivityMetricService::class);
    }

    public function test_output_and_time_records_ingestion_with_provenance(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = \App\Domains\Organization\Models\BusinessUnit::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'name' => 'Operations BU',
            'code' => 'OBU-' . Str::random(4),
        ]);
        $department = Department::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'business_unit_id' => $bu->id,
            'department_name' => 'Manufacturing Operations',
            'department_code' => 'MFG-' . Str::random(4),
        ]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $department->id,
            'employee_code' => 'EMP-PRD-01',
            'employee_number' => '500101',
            'first_name' => 'Ahmad',
            'last_name' => 'Khan',
            'official_email' => 'ahmad.k@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        // 1. Record Output
        $outputRecord = $this->measurementService->recordOutput([
            'tenant_id' => $tenant->id,
            'output_date' => '2026-10-15',
            'employee_id' => $employee->id,
            'department_id' => $department->id,
            'output_type' => 'machined_components',
            'units_completed' => 120,
            'units_defective' => 2,
            'quality_score' => 98.33,
            'source_domain' => 'mes_system',
            'source_record_id' => 'MES-BATCH-9921',
        ]);

        $this->assertDatabaseHas('hcm_productivity_output_records', [
            'id' => $outputRecord->id,
            'source_domain' => 'mes_system',
            'units_completed' => 120,
        ]);

        // 2. Record Time
        $timeRecord = $this->measurementService->recordTime([
            'tenant_id' => $tenant->id,
            'record_date' => '2026-10-15',
            'employee_id' => $employee->id,
            'department_id' => $department->id,
            'category' => 'PRODUCTIVE',
            'hours' => 7.5,
            'source_domain' => 'attendance_punch',
            'source_record_id' => 'PUNCH-4001',
        ]);

        $this->assertDatabaseHas('hcm_productivity_time_records', [
            'id' => $timeRecord->id,
            'category' => 'PRODUCTIVE',
            'hours' => 7.5,
        ]);
    }

    public function test_measurement_calculation_and_idempotency(): void
    {
        $tenant = Tenant::factory()->create();
        $metric = $this->metricService->createMetric([
            'tenant_id' => $tenant->id,
            'code' => 'TICKETS_PER_HOUR',
            'name' => 'Customer Tickets Resolved per Productive Hour',
        ]);

        $idempotencyKey = 'MEAS-2026-10-CS-DEPT-IDEMPOTENT';

        $dto = new ProductivityMeasurementData(
            tenantId: $tenant->id,
            metricDefinitionId: $metric->id,
            metricVersionId: $metric->currentVersion->id,
            departmentId: null,
            locationId: null,
            shiftId: null,
            employeeId: null,
            periodType: 'monthly',
            periodName: '2026-10',
            periodStart: '2026-10-01',
            periodEnd: '2026-10-31',
            outputVolume: 1500.0,
            laborHours: 120.0,
            productiveHours: 100.0,
            availableHours: 120.0,
            laborCost: 3000.0,
            idempotencyKey: $idempotencyKey
        );

        $firstRun = $this->measurementService->calculateMeasurement($dto);
        $this->assertEquals(15.0, $firstRun->productivity_rate);
        $this->assertEquals(2.0, $firstRun->cost_per_unit);
        $this->assertEquals(83.33, $firstRun->utilization_rate);

        // Second run with same idempotency key should return the existing record without duplicate creation
        $secondRun = $this->measurementService->calculateMeasurement($dto);
        $this->assertEquals($firstRun->id, $secondRun->id);
        $this->assertEquals(1, HcmProductivityMeasurement::where('tenant_id', $tenant->id)->count());
    }
}
