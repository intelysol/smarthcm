<?php

namespace Tests\Feature\Onboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingForm;
use App\Domains\Onboarding\Models\HcmOnboardingTemplate;
use App\Domains\Onboarding\Models\HcmOnboardingTemplateVersion;
use App\Domains\Onboarding\Services\OnboardingPolicyAndFormService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingPolicyAndDigitalFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_acknowledgement_and_digital_form_submission(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-POL-01',
            'employee_number' => 'EMP-POL-01',
            'first_name' => 'Michael',
            'last_name' => 'Scott',
            'official_email' => 'michael.scott@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $template = HcmOnboardingTemplate::create(['tenant_id' => $tenant->id, 'name' => 'T', 'code' => 'T-POL']);
        $version = HcmOnboardingTemplateVersion::create(['tenant_id' => $tenant->id, 'template_id' => $template->id, 'version_number' => 1]);

        $case = HcmOnboardingCase::create([
            'tenant_id' => $tenant->id,
            'case_number' => 'ONB-POL-01',
            'employee_id' => $employee->id,
            'template_version_id' => $version->id,
            'start_date' => now()->toDateString(),
        ]);

        $form = HcmOnboardingForm::create([
            'tenant_id' => $tenant->id,
            'name' => 'Emergency Contacts',
            'code' => 'FORM-EMERGENCY',
            'schema_definition' => ['fields' => ['contact_name', 'phone']],
        ]);

        $service = new OnboardingPolicyAndFormService();

        // 1. Policy Acknowledgement
        $ack = $service->acknowledgePolicy($case, 'POLICY-HANDBOOK', 'Employee Handbook', 'v2.1', '192.168.1.50');
        $this->assertEquals('POLICY-HANDBOOK', $ack->policy_code);
        $this->assertEquals('v2.1', $ack->policy_version);
        $this->assertEquals('192.168.1.50', $ack->ip_address);
        $this->assertNotNull($ack->acknowledged_at);

        // 2. Digital Form Submission
        $submission = $service->submitDigitalForm($case, $form, [
            'contact_name' => 'Holly Flax',
            'phone' => '+1-555-0144',
            'relationship' => 'Spouse',
        ]);

        $this->assertEquals($case->id, $submission->case_id);
        $this->assertEquals('Holly Flax', $submission->form_data['contact_name']);
        $this->assertNotNull($submission->submitted_at);
    }
}
