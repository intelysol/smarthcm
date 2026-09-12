<?php

namespace Tests\Feature\Onboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Enums\OnboardingTaskStatus;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingCaseTask;
use App\Domains\Onboarding\Models\HcmOnboardingTemplate;
use App\Domains\Onboarding\Models\HcmOnboardingTemplateVersion;
use App\Domains\Onboarding\Services\OnboardingTaskEngineService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OnboardingTaskEngineAndDependencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_dependency_enforcement_and_cascade_unblocking(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-DEP']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Tech', 'department_code' => 'TECH-DEP']);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-DEP-01',
            'employee_number' => 'EMP-DEP-01',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'official_email' => 'jane.smith@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $template = HcmOnboardingTemplate::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Template',
            'code' => 'TMPL-DEP',
        ]);
        $version = HcmOnboardingTemplateVersion::create([
            'tenant_id' => $tenant->id,
            'template_id' => $template->id,
            'version_number' => 1,
        ]);

        $case = HcmOnboardingCase::create([
            'tenant_id' => $tenant->id,
            'case_number' => 'ONB-DEP-01',
            'employee_id' => $employee->id,
            'template_version_id' => $version->id,
            'start_date' => now()->toDateString(),
        ]);

        // Task A: Document Verification
        $taskA = HcmOnboardingCaseTask::create([
            'tenant_id' => $tenant->id,
            'case_id' => $case->id,
            'title' => 'Verify Identity Documents',
            'task_type' => 'document',
            'owner_role' => 'hr',
            'status' => OnboardingTaskStatus::PENDING->value,
            'is_required' => true,
        ]);

        // Task B: Payroll Setup (Depends on Task A)
        $taskB = HcmOnboardingCaseTask::create([
            'tenant_id' => $tenant->id,
            'case_id' => $case->id,
            'title' => 'Payroll Direct Deposit Setup',
            'task_type' => 'form',
            'owner_role' => 'finance',
            'status' => OnboardingTaskStatus::PENDING->value,
            'is_required' => true,
        ]);

        $engine = new OnboardingTaskEngineService();

        // 1. Add Dependency: Task B depends on Task A
        $engine->addDependency($taskB, $taskA);
        $this->assertEquals(OnboardingTaskStatus::BLOCKED->value, $taskB->fresh()->status);

        // 2. Attempting to complete Task B while Task A is incomplete throws ValidationException
        try {
            $engine->completeTask($taskB, $user->id);
            $this->fail('Expected ValidationException due to unsatisfied prerequisite');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('dependencies', $e->errors());
        }

        // 3. Complete Task A -> unblocks Task B
        $engine->completeTask($taskA, $user->id);
        $this->assertEquals(OnboardingTaskStatus::COMPLETED->value, $taskA->fresh()->status);
        $this->assertEquals(OnboardingTaskStatus::PENDING->value, $taskB->fresh()->status);

        // 4. Progress percentage is 50%
        $this->assertEquals(50.00, $case->fresh()->completion_percentage);

        // 5. Complete Task B -> 100% complete
        $engine->completeTask($taskB, $user->id);
        $this->assertEquals(100.00, $case->fresh()->completion_percentage);
        $this->assertEquals('completed', $case->fresh()->status);
    }
}
