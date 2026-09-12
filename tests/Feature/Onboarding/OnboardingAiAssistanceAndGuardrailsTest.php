<?php

namespace Tests\Feature\Onboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingTemplate;
use App\Domains\Onboarding\Models\HcmOnboardingTemplateVersion;
use App\Domains\Onboarding\Services\OnboardingAiService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingAiAssistanceAndGuardrailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_welcome_generation_readiness_summary_and_guardrails(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-AI-01',
            'employee_number' => 'EMP-AI-01',
            'first_name' => 'Bruce',
            'last_name' => 'Wayne',
            'official_email' => 'bruce@wayne.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $template = HcmOnboardingTemplate::create(['tenant_id' => $tenant->id, 'name' => 'T', 'code' => 'T-AI']);
        $version = HcmOnboardingTemplateVersion::create(['tenant_id' => $tenant->id, 'template_id' => $template->id, 'version_number' => 1]);

        $case = HcmOnboardingCase::create([
            'tenant_id' => $tenant->id,
            'case_number' => 'ONB-AI-01',
            'employee_id' => $employee->id,
            'template_version_id' => $version->id,
            'start_date' => now()->toDateString(),
            'completion_percentage' => 85.00,
        ]);

        $ai = new OnboardingAiService();

        // 1. Welcome Message Drafting
        $welcome = $ai->generateWelcomeMessage($employee, $case);
        $this->assertStringContainsString('Welcome to the team, Bruce!', $welcome['welcome_subject']);
        $this->assertTrue($welcome['is_advisory']);

        // 2. Readiness Summary
        $readiness = $ai->summarizeCaseReadiness($case);
        $this->assertEquals(85.00, $readiness['completion_percentage']);
        $this->assertTrue($readiness['is_advisory']);

        // 3. Safety Guardrail: Adverse probation/termination inquiries blocked
        $blockedResult = $ai->answerOnboardingInquiry($tenant->id, 'Please terminate this employee and fail their probation automatically.');
        $this->assertEquals('blocked_by_guardrails', $blockedResult['status']);
        $this->assertStringContainsString('Blocked by AI HCM Safety Guardrails', $blockedResult['error']);

        // 4. Safe inquiry allowed
        $safeResult = $ai->answerOnboardingInquiry($tenant->id, 'Where does the orientation meeting take place on Monday?');
        $this->assertEquals('success', $safeResult['status']);
        $this->assertStringContainsString('Conference Room A', $safeResult['response']);
    }
}
