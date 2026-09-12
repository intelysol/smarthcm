<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Models\BenefitProvider;
use App\Domains\Benefits\Services\BenefitPayrollReconciliationService;
use App\Domains\Benefits\Services\BenefitProviderIntegrationService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Payroll\Models\PayrollInput;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BenefitProviderAndPayrollIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_export_and_payroll_deduction_reconciliation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $hrUser = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-REC1',
            'employee_number' => '8001',
            'first_name' => 'Toby',
            'last_name' => 'Flenderson',
            'employment_status' => 'active',
            'joining_date' => '2022-01-01',
        ]);

        $employee2 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-REC2',
            'employee_number' => '8002',
            'first_name' => 'Kevin',
            'last_name' => 'Malone',
            'employment_status' => 'active',
            'joining_date' => '2022-01-01',
        ]);

        $provider = BenefitProvider::create([
            'tenant_id' => $tenant->id,
            'provider_code' => 'METLIFE',
            'name' => 'MetLife Dental',
            'is_active' => true,
        ]);

        $plan = BenefitPlan::create([
            'tenant_id' => $tenant->id,
            'code' => 'DENT-MET-01',
            'name' => 'MetLife Preferred Dental',
            'benefit_type' => 'dental',
            'employee_cost' => 40.00,
            'employer_cost' => 60.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
        ]);

        $providerService = app(BenefitProviderIntegrationService::class);
        $reconciliationService = app(BenefitPayrollReconciliationService::class);

        // 1. Map Plan to Provider
        $mapping = $providerService->createOrUpdateMapping($provider, $plan, [
            'external_plan_code' => 'MET-PLN-998',
            'group_number' => 'GRP-5541',
        ], $hrUser);

        $this->assertEquals('MET-PLN-998', $mapping->external_plan_code);

        // 2. Enroll Employees
        $enrollment1 = BenefitEnrollment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee1->id,
            'benefit_plan_id' => $plan->id,
            'effective_from' => '2026-01-01',
            'employee_contribution' => 40.00,
            'employer_contribution' => 60.00,
            'status' => 'approved',
        ]);

        $enrollment2 = BenefitEnrollment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee2->id,
            'benefit_plan_id' => $plan->id,
            'effective_from' => '2026-01-01',
            'employee_contribution' => 40.00,
            'employer_contribution' => 60.00,
            'status' => 'approved',
        ]);

        // 3. Export to Provider
        $integration = $providerService->exportEnrollmentsToProvider($provider, $hrUser);
        $this->assertEquals('sent', $integration->status);
        $this->assertEquals(2, $integration->payload['total_enrollments']);

        // 4. Payroll Reconciliation Test
        $period = PayrollPeriod::create([
            'tenant_id' => $tenant->id,
            'name' => 'September 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'status' => 'active',
        ]);

        // Employee 1: Perfect Match ($40 deduction)
        $input1 = PayrollInput::create([
            'tenant_id' => $tenant->id,
            'payroll_period_id' => $period->id,
            'employee_id' => $employee1->id,
            'status' => 'draft',
        ]);
        $input1->lines()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee1->id,
            'source_module' => 'benefits',
            'source_entity_type' => BenefitEnrollment::class,
            'source_entity_id' => $enrollment1->id,
            'input_type' => 'benefit_deduction',
            'amount' => 40.00,
            'effective_date' => '2026-09-30',
        ]);

        // Employee 2 has NO deduction created in payroll (Missing in Payroll)

        $recResult = $reconciliationService->reconcilePeriod($period, $hrUser);

        $this->assertEquals(2, $recResult['summary']['total_enrollments']);
        $this->assertEquals(1, $recResult['summary']['matched']);
        $this->assertEquals(1, $recResult['summary']['missing_in_payroll']);

        $this->assertDatabaseHas('benefit_reconciliations', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee1->id,
            'status' => 'matched',
            'variance' => 0.00,
        ]);

        $this->assertDatabaseHas('benefit_reconciliations', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee2->id,
            'status' => 'missing_in_payroll',
        ]);
    }
}
