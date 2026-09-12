<?php

declare(strict_types=1);

namespace Tests\Feature\Compensation;

use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Services\CompensationCycleService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\CompensationPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CompensationCycleLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompensationPermissionSeeder::class);
    }

    public function test_cycle_lifecycle_flow_and_validation(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'is_platform_admin' => true]);

        $service = app(CompensationCycleService::class);

        // 1. Create Draft Cycle
        $cycle = $service->create($admin, [
            'name' => 'FY2026 Annual Merit & Salary Review',
            'description' => 'Enterprise annual merit review for all corporate staff',
            'cycle_type' => 'annual_merit',
            'currency' => 'USD',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-03-31',
            'effective_on' => '2026-04-01',
        ]);

        $this->assertEquals('draft', $cycle->status);
        $this->assertEquals('USD', $cycle->currency);

        // 2. Configure guidelines
        $configured = $service->configure($admin, $cycle, [
            'overall_budget_percentage' => 4.5,
            'max_merit_increase' => 12.0,
            'require_calibration_for_exceeds' => true,
        ]);

        $this->assertEquals('configured', $configured->status);
        $this->assertEquals(4.5, $configured->guidelines['overall_budget_percentage']);

        // 3. Valid Transitions
        $open = $service->transition($admin, $configured, 'open');
        $this->assertEquals('open', $open->status);

        $managerPlanning = $service->transition($admin, $open, 'manager_planning');
        $this->assertEquals('manager_planning', $managerPlanning->status);

        $hrReview = $service->transition($admin, $managerPlanning, 'hr_review');
        $this->assertEquals('hr_review', $hrReview->status);

        $calibration = $service->transition($admin, $hrReview, 'calibration');
        $this->assertEquals('calibration', $calibration->status);

        $approval = $service->transition($admin, $calibration, 'approval');
        $this->assertEquals('approval', $approval->status);

        $approved = $service->transition($admin, $approval, 'approved');
        $this->assertEquals('approved', $approved->status);

        // 4. Invalid Transition check
        $this->expectException(ValidationException::class);
        $service->transition($admin, $approved, 'open');
    }
}
