<?php

namespace Tests\Feature\Recruitment;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Position;
use App\Domains\Recruitment\Enums\ApplicationStatus;
use App\Domains\Recruitment\Enums\BackgroundCheckStatus;
use App\Domains\Recruitment\Enums\OfferStatus;
use App\Domains\Recruitment\Models\HcmRecruitmentApplication;
use App\Domains\Recruitment\Models\HcmRecruitmentBackgroundCheck;
use App\Domains\Recruitment\Models\HcmRecruitmentCandidate;
use App\Domains\Recruitment\Models\HcmRecruitmentHiringDecision;
use App\Domains\Recruitment\Models\HcmRecruitmentOffer;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Services\HiringHandoffService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HiringHandoffToCoreHrAndOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_hiring_handoff_requires_accepted_offer_and_passed_checks(): void
    {
        $tenant = Tenant::factory()->create();
        $decisionMaker = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-HIRE']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'IT Operations', 'department_code' => 'OPS']);

        $position = Position::create([
            'tenant_id' => $tenant->id,
            'department_id' => $dept->id,
            'title' => 'Site Reliability Engineer',
            'code' => 'POS-SRE-01',
            'status' => 'active',
        ]);

        $requisition = HcmRecruitmentRequisition::create([
            'tenant_id' => $tenant->id,
            'requisition_number' => 'REQ-HIRE-01',
            'title' => 'SRE Hiring',
            'department_id' => $dept->id,
            'position_id' => $position->id,
            'status' => 'open',
        ]);

        $candidate = HcmRecruitmentCandidate::create([
            'tenant_id' => $tenant->id,
            'candidate_number' => 'CAN-HIRE-01',
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'email' => 'grace@example.com',
            'phone' => '+1-555-8888',
            'status' => 'active',
        ]);

        $application = HcmRecruitmentApplication::create([
            'tenant_id' => $tenant->id,
            'application_number' => 'APP-HIRE-01',
            'candidate_id' => $candidate->id,
            'requisition_id' => $requisition->id,
            'status' => 'offer',
            'applied_at' => now(),
        ]);

        $service = new HiringHandoffService();

        // 1. Cannot hire without accepted offer
        try {
            $service->processHire($application, $decisionMaker->id);
            $this->fail('Expected ValidationException due to missing accepted offer');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('offer', $e->errors());
        }

        // Create accepted offer
        $offer = HcmRecruitmentOffer::create([
            'tenant_id' => $tenant->id,
            'offer_number' => 'OFR-HIRE-01',
            'application_id' => $application->id,
            'candidate_id' => $candidate->id,
            'requisition_id' => $requisition->id,
            'base_salary' => 140000.00,
            'start_date' => now()->addDays(20)->toDateString(),
            'status' => OfferStatus::ACCEPTED->value,
            'accepted_at' => now(),
        ]);

        // 2. Add pending background check -> should block hire
        $check = HcmRecruitmentBackgroundCheck::create([
            'tenant_id' => $tenant->id,
            'application_id' => $application->id,
            'check_type' => 'criminal_history',
            'status' => BackgroundCheckStatus::PENDING->value,
        ]);

        try {
            $service->processHire($application, $decisionMaker->id);
            $this->fail('Expected ValidationException due to pending background check');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('background_checks', $e->errors());
        }

        // Pass background check
        $check->update(['status' => BackgroundCheckStatus::PASSED->value, 'completed_at' => now()]);

        // 3. Finalize Hire
        $decision = $service->processHire($application, $decisionMaker->id, 'Offer accepted and background check passed.');
        $this->assertInstanceOf(HcmRecruitmentHiringDecision::class, $decision);
        $this->assertEquals('hire', $decision->decision);
        $this->assertEquals('handed_off', $decision->handoff_status);
        $this->assertNotNull($decision->core_hr_employee_id);

        // Verify Core HR Employee was created
        $employee = Employee::find($decision->core_hr_employee_id);
        $this->assertNotNull($employee);
        $this->assertEquals('Grace', $employee->first_name);
        $this->assertEquals('Hopper', $employee->last_name);
        $this->assertEquals('grace@example.com', $employee->official_email);

        // Verify Application is now hired
        $this->assertEquals(ApplicationStatus::HIRED->value, $application->fresh()->status);
        $this->assertEquals('hired', $candidate->fresh()->status);
    }
}
