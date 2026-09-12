<?php

declare(strict_types=1);

namespace Tests\Feature\Compensation;

use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Models\CompensationRecommendation;
use App\Domains\Compensation\Services\CompensationPayrollIntegrationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\CompensationPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompensationPayrollIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompensationPermissionSeeder::class);
    }

    public function test_approved_plan_export_to_payroll(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $hrAdmin = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        $company = \App\Domains\Organization\Models\Company::factory()->create(['tenant_id' => $tenant->id]);

        $emp1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-PAY-001',
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'employment_status' => 'active',
            'joining_date' => '2021-01-01',
        ]);

        $emp2 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-PAY-002',
            'first_name' => 'Dana',
            'last_name' => 'White',
            'employment_status' => 'active',
            'joining_date' => '2020-05-15',
        ]);

        $cycle = CompensationCycle::create([
            'tenant_id' => $tenant->id,
            'name' => '2026 Spring Merit Cycle',
            'cycle_type' => 'annual_merit',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-03-31',
            'effective_on' => '2026-04-01',
            'status' => 'approved',
            'currency' => 'USD',
        ]);

        $rec1 = CompensationRecommendation::create([
            'tenant_id' => $tenant->id,
            'compensation_cycle_id' => $cycle->id,
            'employee_id' => $emp1->id,
            'current_base_salary' => 90000.0,
            'recommended_base_salary' => 94500.0,
            'increase_amount' => 4500.0,
            'increase_percentage' => 5.0,
            'recommendation_type' => 'merit',
            'status' => 'approved',
        ]);

        $rec2 = CompensationRecommendation::create([
            'tenant_id' => $tenant->id,
            'compensation_cycle_id' => $cycle->id,
            'employee_id' => $emp2->id,
            'current_base_salary' => 110000.0,
            'recommended_base_salary' => 118800.0,
            'increase_amount' => 8800.0,
            'increase_percentage' => 8.0,
            'recommendation_type' => 'merit',
            'status' => 'approved',
        ]);

        $service = app(CompensationPayrollIntegrationService::class);

        // Export approved records to payroll adapter
        $export = $service->exportApprovedCycleToPayroll($hrAdmin, $cycle);

        $this->assertEquals(2, $export->record_count);
        $this->assertEquals(13300.0, (float) $export->total_increase_payroll_impact);
        $this->assertEquals('exported', $export->status);
        $this->assertEquals($hrAdmin->id, $export->exported_by);
        $this->assertStringStartsWith('PAYROLL-COMP-', $export->batch_reference);

        // Verify recommendation status transition to exported
        $rec1->refresh();
        $rec2->refresh();
        $this->assertEquals('exported', $rec1->status);
        $this->assertEquals('exported', $rec2->status);

        // Update integration status to applied
        $updated = $service->updateIntegrationStatus($export, 'applied', ['applied_at' => now()->toIso8601String()]);
        $this->assertEquals('applied', $updated->status);
    }
}
