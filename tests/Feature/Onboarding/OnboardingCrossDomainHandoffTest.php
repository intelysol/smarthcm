<?php

namespace Tests\Feature\Onboarding;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Enums\OnboardingCaseStatus;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Services\OnboardingCaseService;
use App\Domains\Organization\Models\Company;
use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentOffer;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingCrossDomainHandoffTest extends TestCase
{
    use RefreshDatabase;

    public function test_recruitment_to_core_hr_to_onboarding_handoff(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        // 1. Recruitment candidate & requisition
        $candidate = HcmRecruitmentCandidate::create([
            'tenant_id' => $tenant->id,
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'email' => 'grace.hopper@example.com',
            'candidate_number' => 'CAND-GH-01',
        ]);

        $requisition = HcmRecruitmentRequisition::create([
            'tenant_id' => $tenant->id,
            'requisition_number' => 'REQ-GH-01',
            'title' => 'Senior Systems Architect',
            'headcount' => 1,
        ]);

        $application = HcmRecruitmentApplication::create([
            'tenant_id' => $tenant->id,
            'application_number' => 'APP-GH-01',
            'requisition_id' => $requisition->id,
            'candidate_id' => $candidate->id,
            'status' => 'offer',
            'applied_at' => now(),
        ]);

        $offer = HcmRecruitmentOffer::create([
            'tenant_id' => $tenant->id,
            'offer_number' => 'OFF-GH-01',
            'application_id' => $application->id,
            'requisition_id' => $requisition->id,
            'candidate_id' => $candidate->id,
            'base_salary' => 185000,
            'start_date' => now()->addDays(14)->toDateString(),
            'status' => 'accepted',
        ]);

        // 2. Core HR Employee Created
        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-GH-01',
            'employee_number' => 'EMP-GH-01',
            'first_name' => $candidate->first_name,
            'last_name' => $candidate->last_name,
            'official_email' => $candidate->email,
            'joining_date' => now()->addDays(14)->toDateString(),
            'employment_status' => 'active',
        ]);

        // 3. Onboarding Case initialized from recruitment handoff
        $service = new OnboardingCaseService();
        $case = $service->initializeCaseForEmployee($employee, [
            'recruitment_application_id' => $application->id,
            'offer_id' => $offer->id,
            'start_date' => $employee->joining_date,
        ]);

        $this->assertInstanceOf(HcmOnboardingCase::class, $case);
        $this->assertEquals($application->id, $case->recruitment_application_id);
        $this->assertEquals($offer->id, $case->offer_id);
        $this->assertEquals($employee->id, $case->employee_id);
        $this->assertEquals(OnboardingCaseStatus::PREBOARDING->value, $case->status);
    }
}
