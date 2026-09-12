<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Services\BenefitWaiverService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BenefitWaiverAndMandatoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_mandatory_plans_cannot_be_waived_and_waivable_plans_require_approval(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $hrAdmin = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-WAIVE',
            'employee_number' => '4444',
            'first_name' => 'Dwight',
            'last_name' => 'Schrute',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $mandatoryPlan = BenefitPlan::create([
            'tenant_id' => $tenant->id,
            'code' => 'STAT-LIFE',
            'name' => 'Mandatory Group Life Insurance',
            'benefit_type' => 'life_insurance',
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'is_mandatory' => true,
            'is_waivable' => false,
        ]);

        $optionalPlan = BenefitPlan::create([
            'tenant_id' => $tenant->id,
            'code' => 'OPT-DENTAL',
            'name' => 'Optional Vision Plus',
            'benefit_type' => 'vision',
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'is_mandatory' => false,
            'is_waivable' => true,
        ]);

        $waiverService = app(BenefitWaiverService::class);

        // 1. Attempt waiver on mandatory plan -> Must fail!
        try {
            $waiverService->submitWaiver($employee, $mandatoryPlan, [
                'reason' => 'I already have personal farm life insurance.',
            ]);
            $this->fail('Expected ValidationException when attempting to waive mandatory benefit.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('plan', $e->errors());
        }

        // 2. Submit waiver on optional plan -> Success
        $waiver = $waiverService->submitWaiver($employee, $optionalPlan, [
            'reason' => 'Covered by spouse employer vision plan.',
            'waiver_date' => '2026-11-20',
        ]);

        $this->assertEquals('submitted', $waiver->status);
        $this->assertDatabaseHas('benefit_elections', [
            'employee_id' => $employee->id,
            'benefit_plan_id' => $optionalPlan->id,
            'is_waived' => true,
        ]);

        // 3. HR approves waiver
        $approvedWaiver = $waiverService->approveWaiver($waiver, $hrAdmin);
        $this->assertEquals('approved', $approvedWaiver->status);
        $this->assertEquals($hrAdmin->id, $approvedWaiver->approved_by);
    }
}
