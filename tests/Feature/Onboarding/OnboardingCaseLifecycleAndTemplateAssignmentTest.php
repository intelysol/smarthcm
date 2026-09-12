<?php

namespace Tests\Feature\Onboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Enums\OnboardingCaseStatus;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingTemplate;
use App\Domains\Onboarding\Models\HcmOnboardingTemplateTask;
use App\Domains\Onboarding\Models\HcmOnboardingTemplateVersion;
use App\Domains\Onboarding\Services\OnboardingCaseService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingCaseLifecycleAndTemplateAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarding_case_lifecycle_and_template_task_generation(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-ONB']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Engineering', 'department_code' => 'ENG-ONB']);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-ONB-01',
            'employee_number' => 'EMP-ONB-01',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'official_email' => 'john.doe@example.com',
            'joining_date' => now()->addDays(10)->toDateString(),
            'employment_status' => 'active',
        ]);

        // Create department-specific template
        $template = HcmOnboardingTemplate::create([
            'tenant_id' => $tenant->id,
            'name' => 'Engineering Onboarding',
            'code' => 'TMPL-ENG',
            'department_id' => $dept->id,
            'is_active' => true,
        ]);

        $version = HcmOnboardingTemplateVersion::create([
            'tenant_id' => $tenant->id,
            'template_id' => $template->id,
            'version_number' => 1,
            'status' => 'published',
        ]);

        HcmOnboardingTemplateTask::create([
            'tenant_id' => $tenant->id,
            'template_version_id' => $version->id,
            'title' => 'Complete Preboarding Profile',
            'task_type' => 'form',
            'owner_role' => 'employee',
            'due_offset_days' => -3,
            'is_required' => true,
        ]);

        HcmOnboardingTemplateTask::create([
            'tenant_id' => $tenant->id,
            'template_version_id' => $version->id,
            'title' => 'Setup Cloud Development Environment',
            'task_type' => 'it_provisioning',
            'owner_role' => 'employee',
            'due_offset_days' => 1,
            'is_required' => true,
        ]);

        $service = new OnboardingCaseService();

        // 1. Initialize Case
        $case = $service->initializeCaseForEmployee($employee);

        $this->assertInstanceOf(HcmOnboardingCase::class, $case);
        $this->assertEquals(OnboardingCaseStatus::PREBOARDING->value, $case->status);
        $this->assertStringStartsWith('ONB-', $case->case_number);
        $this->assertEquals($version->id, $case->template_version_id);

        // 2. Concrete tasks generated
        $this->assertCount(2, $case->tasks);
        $this->assertEquals(0.00, $case->completion_percentage);

        // 3. Document Requirements initialized
        $this->assertCount(4, $case->documentRequirements);

        // 4. Probation initialized
        $this->assertNotNull($case->probation);
        $this->assertEquals('in_progress', $case->probation->status);
    }
}
