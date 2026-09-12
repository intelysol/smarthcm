<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Services\BenefitElectionService;
use App\Domains\Benefits\Services\BenefitOpenEnrollmentService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BenefitOpenEnrollmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_enrollment_lifecycle_progress_tracking_and_finalization(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $hrAdmin = User::factory()->create(['tenant_id' => $tenant->id]);

        $emp1 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-OE1',
            'employee_number' => '5001',
            'first_name' => 'Jim',
            'last_name' => 'Halpert',
            'employment_status' => 'active',
            'joining_date' => '2024-01-01',
        ]);

        $emp2 = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-OE2',
            'employee_number' => '5002',
            'first_name' => 'Pam',
            'last_name' => 'Beesly',
            'employment_status' => 'active',
            'joining_date' => '2024-01-01',
        ]);

        $plan = BenefitPlan::create([
            'tenant_id' => $tenant->id,
            'code' => 'MED-OE-2027',
            'name' => '2027 Medical Plan',
            'benefit_type' => 'medical',
            'employee_cost' => 80.00,
            'employer_cost' => 300.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'is_waivable' => true,
        ]);

        $oeService = app(BenefitOpenEnrollmentService::class);
        $electionService = app(BenefitElectionService::class);

        // 1. Create and Open Campaign
        $window = $oeService->createWindow($tenant->id, [
            'name' => '2027 Annual Open Enrollment',
            'plan_year' => 2027,
            'start_date' => '2026-11-01',
            'close_date' => '2026-11-30',
            'effective_date' => '2027-01-01',
        ], $hrAdmin);

        $this->assertEquals('draft', $window->status);
        $window = $oeService->openWindow($window, $hrAdmin);
        $this->assertEquals('open', $window->status);

        // 2. Employee 1 submits election
        $election1 = $electionService->elect($emp1, $plan, [
            'benefit_enrollment_window_id' => $window->id,
            'coverage_level' => 'employee_only',
            'election_date' => '2026-11-10',
            'effective_date' => '2027-01-01',
        ]);
        $electionService->confirmElection($election1);

        // 3. Employee 2 waives plan
        $election2 = $electionService->elect($emp2, $plan, [
            'benefit_enrollment_window_id' => $window->id,
            'is_waived' => true,
            'waiver_reason' => 'Covered under spouse plan.',
            'election_date' => '2026-11-12',
            'effective_date' => '2027-01-01',
        ]);
        $electionService->confirmElection($election2);

        // 4. Progress check
        $progress = $oeService->getProgress($window);
        $this->assertEquals(2, $progress['total_eligible_population']);
        $this->assertEquals(1, $progress['completed']);
        $this->assertEquals(1, $progress['waived']);
        $this->assertEquals(0, $progress['not_started']);
        $this->assertEquals(100.0, $progress['completion_percentage']);

        // 5. Finalize Window
        $finalized = $oeService->finalizeWindow($window, $hrAdmin);
        $this->assertEquals('locked', $finalized->status);

        // Emp 1 election should now be an active enrollment
        $this->assertDatabaseHas('benefit_enrollments', [
            'tenant_id' => $tenant->id,
            'employee_id' => $emp1->id,
            'benefit_plan_id' => $plan->id,
            'status' => 'approved',
        ]);
    }
}
