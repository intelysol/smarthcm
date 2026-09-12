<?php

namespace Tests\Feature\WorkforceOptimization;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforceOptimization\Services\WorkforceOpportunityService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WorkforceOpportunityDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
    }

    public function test_detects_capacity_deficit_and_surplus_opportunities(): void
    {
        $company = Company::factory()->create(['tenant_id' => $this->tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'name' => 'Operations BU', 'code' => 'OPS']);
        $dept1 = Department::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'department_name' => 'Logistics', 'department_code' => 'LOG-01', 'business_unit_id' => $bu->id]);
        $dept2 = Department::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'department_name' => 'Warehousing', 'department_code' => 'WH-01', 'business_unit_id' => $bu->id]);

        // Deficit plan for Dept 1
        $plan1Id = (string) \Illuminate\Support\Str::uuid();
        DB::table('hcm_workforce_plans')->insert([
            'id' => $plan1Id,
            'tenant_id' => $this->tenant->id,
            'code' => 'CAP_DEFICIT_01',
            'name' => 'Deficit Plan',
            'planning_cycle' => 'FY2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'department_id' => $dept1->id,
            'business_unit_id' => $bu->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $capPlan1Id = (string) \Illuminate\Support\Str::uuid();
        DB::table('hcm_workforce_capacity_plans')->insert([
            'id' => $capPlan1Id,
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan1Id,
            'capacity_metric_name' => 'shipments_per_worker',
            'workload_volume' => 1000,
            'ratio_per_fte' => 200,
            'calculated_required_fte' => 5.0, // 5*160 = 800h vs 0h => gap = 800
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Surplus plan for Dept 2 (4 active workers = 640h available, 1 FTE = 160h required)
        for ($i = 1; $i <= 4; $i++) {
            Employee::create([
                'tenant_id' => $this->tenant->id,
                'company_id' => $company->id,
                'department_id' => $dept2->id,
                'employee_number' => 'EMP-WH-'.$i,
                'employee_code' => 'EWH-'.$i,
                'first_name' => 'Ware',
                'last_name' => 'Worker '.$i,
                'employment_status' => 'active',
                'joining_date' => '2025-01-01',
            ]);
        }
        $plan2Id = (string) \Illuminate\Support\Str::uuid();
        DB::table('hcm_workforce_plans')->insert([
            'id' => $plan2Id,
            'tenant_id' => $this->tenant->id,
            'code' => 'CAP_SURPLUS_01',
            'name' => 'Surplus Plan',
            'planning_cycle' => 'FY2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'department_id' => $dept2->id,
            'business_unit_id' => $bu->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $capPlan2Id = (string) \Illuminate\Support\Str::uuid();
        DB::table('hcm_workforce_capacity_plans')->insert([
            'id' => $capPlan2Id,
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan2Id,
            'capacity_metric_name' => 'shipments_per_worker',
            'workload_volume' => 100,
            'ratio_per_fte' => 100,
            'calculated_required_fte' => 1.0, // 1*160 = 160h vs 640h => gap = -480
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(WorkforceOpportunityService::class);
        $opportunities = $service->detectOpportunities($this->tenant->id);

        $this->assertNotEmpty($opportunities);

        $this->assertDatabaseHas('hcm_workforce_optimization_opportunities', [
            'tenant_id' => $this->tenant->id,
            'opportunity_code' => 'CAP_GAP_PLAN_'.$capPlan1Id,
            'category' => 'CAPACITY_GAP',
        ]);

        $this->assertDatabaseHas('hcm_workforce_optimization_opportunities', [
            'tenant_id' => $this->tenant->id,
            'opportunity_code' => 'CAP_SURPLUS_PLAN_'.$capPlan2Id,
            'category' => 'CAPACITY_SURPLUS',
        ]);
    }

    public function test_detects_overtime_anomaly_opportunities(): void
    {
        $company = Company::factory()->create(['tenant_id' => $this->tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'name' => 'Manufacturing BU', 'code' => 'MFG']);
        $dept = Department::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'department_name' => 'Assembly', 'department_code' => 'ASY-01', 'business_unit_id' => $bu->id]);

        $metricId = (string) \Illuminate\Support\Str::uuid();
        DB::table('hcm_productivity_metric_definitions')->insert([
            'id' => $metricId,
            'tenant_id' => $this->tenant->id,
            'code' => 'METRIC_OT_TEST',
            'name' => 'Overtime Metric',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('hcm_productivity_measurements')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'metric_definition_id' => $metricId,
            'department_id' => $dept->id,
            'period_type' => 'monthly',
            'period_name' => '2026-09',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'overtime_hours' => 120.0,
            'labor_hours' => 2400.0,
            'productive_hours' => 2200.0,
            'output_volume' => 5000.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(WorkforceOpportunityService::class);
        $opportunities = $service->detectOpportunities($this->tenant->id);

        $this->assertDatabaseHas('hcm_workforce_optimization_opportunities', [
            'tenant_id' => $this->tenant->id,
            'category' => 'OVERTIME_ANOMALY',
            'department_id' => $dept->id,
        ]);
    }
}
