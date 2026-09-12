<?php

namespace Tests\Feature\Mobility;

use App\Domains\Employee\Models\Employee;
use App\Domains\Mobility\Enums\AssignmentStatus;
use App\Domains\Mobility\Enums\EligibilityStatus;
use App\Domains\Mobility\Enums\MobilityRequestStatus;
use App\Domains\Mobility\Models\MobilityProgram;
use App\Domains\Mobility\Models\MobilityRequest;
use App\Domains\Mobility\Services\MobilityAssignmentService;
use App\Domains\Mobility\Services\MobilityEligibilityService;
use App\Domains\Mobility\Services\MobilityRequestService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobilityRequestAndEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobility_request_creation_evaluates_eligibility_and_creates_assignment(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $homeCompany = Company::factory()->create(['tenant_id' => $tenant->id, 'country' => 'United States']);
        $hostCompany = Company::factory()->create(['tenant_id' => $tenant->id, 'country' => 'United Kingdom']);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $homeCompany->id,
            'employee_code' => 'EMP-1001',
            'employee_number' => 'EMP-1001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'official_email' => 'john.doe@example.com',
            'employment_status' => 'active',
            'joining_date' => '2025-01-01',
        ]);

        $program = MobilityProgram::create([
            'tenant_id' => $tenant->id,
            'code' => 'EXP-LONG-TERM',
            'name' => 'Long-Term Expatriate Program',
            'mobility_type' => 'international_assignment',
            'min_duration_months' => 6,
            'max_duration_months' => 36,
            'requires_relocation' => true,
            'requires_compliance_check' => true,
            'is_active' => true,
        ]);

        $requestService = app(MobilityRequestService::class);

        // 1. Create Request
        $request = $requestService->createRequest([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'program_id' => $program->id,
            'mobility_type' => 'international_assignment',
            'home_company_id' => $homeCompany->id,
            'home_country' => 'United States',
            'host_company_id' => $hostCompany->id,
            'host_country' => 'United Kingdom',
            'proposed_start_date' => '2026-10-01',
            'proposed_end_date' => '2027-10-01',
            'duration_months' => 12,
            'business_justification' => 'Expansion of London R&D engineering facility.',
            'assignment_reason' => 'Technical Leadership',
        ], $user);

        $this->assertDatabaseHas('hcm_mobility_requests', [
            'id' => $request->id,
            'status' => MobilityRequestStatus::DRAFT->value,
            'eligibility_status' => EligibilityStatus::ELIGIBLE->value,
        ]);

        Carbon::setTestNow('2026-09-01 11:00:00');

        // 2. Submit Request
        $requestService->submitRequest($request, $user);
        $this->assertEquals(MobilityRequestStatus::SUBMITTED, $request->fresh()->status);

        Carbon::setTestNow('2026-09-01 12:00:00');

        // 3. Approve Request
        $requestService->approveRequest($request, $user);
        $this->assertEquals(MobilityRequestStatus::APPROVED, $request->fresh()->status);
        $this->assertEquals($user->id, $request->fresh()->approved_by);

        Carbon::setTestNow('2026-09-01 13:00:00');

        // 4. Create Assignment from Approved Request
        $assignmentService = app(MobilityAssignmentService::class);
        $assignment = $assignmentService->createFromRequest($request, $user);

        $this->assertDatabaseHas('hcm_mobility_assignments', [
            'id' => $assignment->id,
            'mobility_request_id' => $request->id,
            'employee_id' => $employee->id,
            'status' => AssignmentStatus::PLANNING->value,
            'current_version' => 1,
            'home_country' => 'United States',
            'host_country' => 'United Kingdom',
        ]);

        $this->assertEquals(1, $assignment->versions()->count());
        $this->assertNotNull($assignment->terms);
        $this->assertEquals(2, $assignment->locations()->count());

        Carbon::setTestNow('2026-09-01 14:00:00');

        // 5. Activate Assignment
        $assignmentService->activateAssignment($assignment, $user);
        $this->assertEquals(AssignmentStatus::ACTIVE, $assignment->fresh()->status);

        // Snapshot generated for version update
        $this->assertEquals(2, $assignment->versions()->count());
    }

    public function test_eligibility_fails_for_inactive_employee_or_short_tenure(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');

        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $inactiveEmp = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-2001',
            'employee_number' => 'EMP-2001',
            'first_name' => 'Bob',
            'last_name' => 'Inactive',
            'official_email' => 'bob@example.com',
            'employment_status' => 'suspended',
            'joining_date' => '2024-01-01',
        ]);

        $shortTenureEmp = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-2002',
            'employee_number' => 'EMP-2002',
            'first_name' => 'Charlie',
            'last_name' => 'Newbie',
            'official_email' => 'charlie@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-08-01', // 1 month of service
        ]);

        $eligibilityService = app(MobilityEligibilityService::class);

        $resultInactive = $eligibilityService->evaluateEligibility($inactiveEmp);
        $this->assertEquals(EligibilityStatus::INELIGIBLE, $resultInactive['status']);
        $this->assertFalse($resultInactive['is_eligible']);

        $resultShortTenure = $eligibilityService->evaluateEligibility($shortTenureEmp);
        $this->assertEquals(EligibilityStatus::REQUIRES_REVIEW, $resultShortTenure['status']);
        $this->assertTrue($resultShortTenure['requires_review']);
    }
}
