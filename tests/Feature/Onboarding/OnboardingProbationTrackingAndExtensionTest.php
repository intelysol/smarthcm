<?php

namespace Tests\Feature\Onboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Enums\ProbationStatus;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Models\HcmOnboardingProbation;
use App\Domains\Onboarding\Models\HcmOnboardingTemplate;
use App\Domains\Onboarding\Models\HcmOnboardingTemplateVersion;
use App\Domains\Onboarding\Services\OnboardingProbationService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OnboardingProbationTrackingAndExtensionTest extends TestCase
{
    use RefreshDatabase;

    public function test_probation_tracking_extension_and_review(): void
    {
        $tenant = Tenant::factory()->create();
        $managerUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-PROB-01',
            'employee_number' => 'EMP-PROB-01',
            'first_name' => 'Pam',
            'last_name' => 'Beesly',
            'official_email' => 'pam@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $template = HcmOnboardingTemplate::create(['tenant_id' => $tenant->id, 'name' => 'T', 'code' => 'T-PROB']);
        $version = HcmOnboardingTemplateVersion::create(['tenant_id' => $tenant->id, 'template_id' => $template->id, 'version_number' => 1]);

        $case = HcmOnboardingCase::create([
            'tenant_id' => $tenant->id,
            'case_number' => 'ONB-PROB-01',
            'employee_id' => $employee->id,
            'template_version_id' => $version->id,
            'start_date' => now()->toDateString(),
        ]);

        $probation = HcmOnboardingProbation::create([
            'tenant_id' => $tenant->id,
            'case_id' => $case->id,
            'employee_id' => $employee->id,
            'probation_start_date' => now()->toDateString(),
            'probation_end_date' => now()->addDays(90)->toDateString(),
            'status' => ProbationStatus::IN_PROGRESS->value,
        ]);

        $service = new OnboardingProbationService();

        // 1. Extend probation
        $extended = $service->extendProbation($probation, now()->addDays(120)->toDateString(), 'Additional time needed for certifications.');
        $this->assertEquals(ProbationStatus::EXTENDED->value, $extended->status);
        $this->assertNotNull($extended->extended_to_date);

        // Invalid extension (date in past or earlier than end date)
        try {
            $service->extendProbation($probation, now()->addDays(30)->toDateString(), 'Invalid date');
            $this->fail('Expected ValidationException on invalid probation extension date');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('new_end_date', $e->errors());
        }

        // 2. Complete probation review -> Pass & Confirm
        $review = $service->completeProbationReview($extended, $managerUser->id, [
            'performance_rating' => 4.5,
            'recommendation' => 'pass',
            'comments' => 'Outstanding progress and team collaboration.',
        ]);

        $this->assertEquals(ProbationStatus::PASSED->value, $extended->fresh()->status);
        $this->assertEquals('confirmed', $extended->fresh()->outcome);
        $this->assertEquals(4.5, $review->performance_rating);
    }
}
