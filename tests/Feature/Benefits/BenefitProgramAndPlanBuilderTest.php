<?php

namespace Tests\Feature\Benefits;

use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Models\BenefitProgram;
use App\Domains\Benefits\Services\BenefitCoverageService;
use App\Domains\Benefits\Services\BenefitProgramService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BenefitProgramAndPlanBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_creation_versioning_and_plan_assignment(): void
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create(['tenant_id' => $tenant->id]);

        $programService = app(BenefitProgramService::class);
        $coverageService = app(BenefitCoverageService::class);

        // 1. Create Program
        $program = $programService->createProgram($tenant->id, [
            'code' => 'HEALTH-WELLNESS',
            'name' => 'Health & Wellness Umbrella',
            'category' => 'health_medical',
            'description' => 'Umbrella program covering medical, dental, and vision.',
            'status' => 'active',
            'configuration' => ['allow_fsa' => true],
        ], $admin);

        $this->assertDatabaseHas('benefit_programs', ['code' => 'HEALTH-WELLNESS']);
        $this->assertDatabaseHas('benefit_program_versions', ['benefit_program_id' => $program->id, 'version_number' => 1]);

        // 2. Create Plan and assign to Program
        $plan = BenefitPlan::create([
            'tenant_id' => $tenant->id,
            'code' => 'MED-GOLD',
            'name' => 'Gold Medical HMO',
            'benefit_type' => 'medical',
            'employee_cost' => 50.0000,
            'employer_cost' => 200.0000,
            'annual_limit' => 500000.0000,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'is_mandatory' => false,
            'is_waivable' => true,
        ]);

        $programService->assignPlanToProgram($program, $plan, $admin);

        $this->assertEquals($program->id, $plan->fresh()->benefit_program_id);

        // 3. Create Coverage Tiers
        $coverageEmpOnly = $coverageService->createCoverage($plan, [
            'code' => 'EMP_ONLY',
            'name' => 'employee_only',
            'coverage_multiplier' => 1.0000,
            'employee_cost_factor' => 1.0000,
            'employer_cost_factor' => 1.0000,
            'max_dependents' => 0,
        ], $admin);

        $coverageFamily = $coverageService->createCoverage($plan, [
            'code' => 'FAMILY',
            'name' => 'family',
            'coverage_multiplier' => 2.5000,
            'employee_cost_factor' => 2.0000,
            'employer_cost_factor' => 2.5000,
            'max_dependents' => 5,
        ], $admin);

        $this->assertDatabaseHas('benefit_coverages', ['code' => 'FAMILY', 'benefit_plan_id' => $plan->id]);

        // 4. Calculate estimated costs
        $costEmpOnly = $coverageService->calculateEstimatedCosts($plan, $coverageEmpOnly);
        $this->assertEquals(50.00, $costEmpOnly['employee_cost']);
        $this->assertEquals(200.00, $costEmpOnly['employer_cost']);

        $costFamily = $coverageService->calculateEstimatedCosts($plan, $coverageFamily);
        $this->assertEquals(100.00, $costFamily['employee_cost']); // 50 * 2.0
        $this->assertEquals(500.00, $costFamily['employer_cost']); // 200 * 2.5
        $this->assertEquals(600.00, $costFamily['total_cost']);
    }
}
