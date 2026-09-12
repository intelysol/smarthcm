<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Services\BenefitsAiAdvisoryService;
use App\Domains\Benefits\Services\BenefitStatementService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BenefitsAiAdvisoryAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_advisory_safeguards_statements_and_tamper_evident_audit(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-AI-01',
            'employee_number' => '9901',
            'first_name' => 'Oscar',
            'last_name' => 'Martinez',
            'employment_status' => 'active',
            'joining_date' => '2022-01-01',
        ]);

        $plan1 = BenefitPlan::create([
            'tenant_id' => $tenant->id,
            'code' => 'HEALTH-HMO',
            'name' => 'Standard HMO',
            'benefit_type' => 'medical',
            'employee_cost' => 50.00,
            'employer_cost' => 200.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
        ]);

        $plan2 = BenefitPlan::create([
            'tenant_id' => $tenant->id,
            'code' => 'HEALTH-PPO',
            'name' => 'Premium PPO',
            'benefit_type' => 'medical',
            'employee_cost' => 120.00,
            'employer_cost' => 350.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
        ]);

        $aiService = app(BenefitsAiAdvisoryService::class);
        $statementService = app(BenefitStatementService::class);
        $auditService = app(AuditService::class);

        // 1. AI explains available eligible plans
        $explained = $aiService->explainAvailablePlans($employee);
        $this->assertCount(2, $explained['eligible_plans']);
        $this->assertStringContainsString('not provide medical advice', $explained['disclaimer']);

        // 2. AI Plan Comparison
        $compared = $aiService->comparePlans([$plan1->id, $plan2->id], $employee);
        $this->assertCount(2, $compared['comparison']);
        $this->assertStringContainsString('Comparison generated purely from active plan configurations', $compared['factual_notes']);

        // 3. AI Safety Boundary: Rejects medical advice or disease diagnosis
        $prohibitedPrompt = "I have chest pain and shortness of breath, which health insurance plan should I pick for treatment?";
        $blockedResponse = $aiService->processAdvisoryPrompt($prohibitedPrompt, $employee);
        $this->assertEquals('prohibited_topic', $blockedResponse['type']);
        $this->assertTrue($blockedResponse['is_medical_advice_prevented']);
        $this->assertStringContainsString('cannot provide medical advice', $blockedResponse['message']);

        // 4. Benefit Statement Generation
        BenefitEnrollment::create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'benefit_plan_id' => $plan2->id,
            'effective_from' => '2026-01-01',
            'employee_contribution' => 120.00,
            'employer_contribution' => 350.00,
            'status' => 'approved',
        ]);

        $statement = $statementService->generateStatement($employee, 2026, $user);
        $this->assertEquals(4200.00, (float) $statement->total_employer_cost); // 350 * 12
        $this->assertEquals(1440.00, (float) $statement->total_employee_cost); // 120 * 12
        $this->assertEquals(5640.00, (float) $statement->total_benefit_value);

        // 5. Tamper-evident Audit Verification
        $this->assertTrue($auditService->verify($tenant->id));
    }
}
