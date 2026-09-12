<?php

declare(strict_types=1);

namespace Tests\Feature\Compensation;

use App\Domains\Compensation\Models\CompensationBand;
use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Models\CompensationMeritMatrix;
use App\Domains\Compensation\Services\MeritPlanningService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\JobGrade;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\CompensationPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeritPlanningWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompensationPermissionSeeder::class);
    }

    public function test_merit_proposal_guidelines_and_approval(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);
        $approver = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        $grade = JobGrade::create([
            'tenant_id' => $tenant->id,
            'grade_code' => 'L5-ENG',
            'grade_name' => 'Senior Engineer L5',
            'level' => 5,
        ]);

        $company = \App\Domains\Organization\Models\Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-MERIT-001',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'job_grade_id' => $grade->id,
            'employment_status' => 'active',
            'joining_date' => '2023-01-01',
            'gender' => 'female',
        ]);

        $cycle = CompensationCycle::create([
            'tenant_id' => $tenant->id,
            'name' => '2026 Annual Review',
            'cycle_type' => 'annual_merit',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'effective_on' => '2026-04-01',
            'status' => 'manager_planning',
            'currency' => 'USD',
        ]);

        // Define Salary Band (Min: $80,000, Mid: $100,000, Max: $120,000)
        CompensationBand::create([
            'tenant_id' => $tenant->id,
            'code' => 'BAND-L5-ENG',
            'name' => 'Level 5 Engineering Band',
            'job_grade_id' => $grade->id,
            'currency' => 'USD',
            'minimum' => 80000.0,
            'midpoint' => 100000.0,
            'maximum' => 120000.0,
            'effective_from' => '2026-01-01',
        ]);

        // Merit Matrix
        CompensationMeritMatrix::create([
            'tenant_id' => $tenant->id,
            'compensation_cycle_id' => $cycle->id,
            'name' => 'Standard Merit Matrix',
            'matrix_grid' => [
                ['rating' => 'exceeds', 'bracket' => 'under_80', 'min_pct' => 5.0, 'target_pct' => 7.0, 'max_pct' => 9.0],
                ['rating' => 'exceeds', 'bracket' => '80_to_95', 'min_pct' => 4.0, 'target_pct' => 6.0, 'max_pct' => 8.0],
                ['rating' => 'exceeds', 'bracket' => '95_to_105', 'min_pct' => 3.5, 'target_pct' => 5.0, 'max_pct' => 6.5],
            ],
            'is_active' => true,
        ]);

        $service = app(MeritPlanningService::class);

        // Manager proposes a 5.0% merit increase
        $recommendation = $service->generateOrUpdateRecommendation(
            $manager,
            $cycle,
            $employee,
            5.0,
            'exceeds',
            'Outstanding delivery on key microservices migration project.'
        );

        $this->assertEquals('proposed', $recommendation->status);
        $this->assertEquals(5.0, (float) $recommendation->increase_percentage);
        $this->assertEquals(3000.0, (float) $recommendation->increase_amount); // 5% of 60,000 baseline
        $this->assertEquals(63000.0, (float) $recommendation->recommended_base_salary);
        $this->assertEquals($manager->id, $recommendation->proposed_by);

        // Approver approves recommendation
        $approved = $service->approveRecommendation($approver, $recommendation);
        $this->assertEquals('approved', $approved->status);
        $this->assertEquals($approver->id, $approved->approved_by);
        $this->assertNotNull($approved->approved_at);
    }
}
