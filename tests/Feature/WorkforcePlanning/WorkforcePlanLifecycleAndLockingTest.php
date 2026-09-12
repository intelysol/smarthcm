<?php

namespace Tests\Feature\WorkforcePlanning;

use App\Domains\Shared\Models\Tenant;
use App\Domains\WorkforcePlanning\Enums\WorkforcePlanStatus;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WorkforcePlanLifecycleAndLockingTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_lifecycle_and_immutability_locking(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $service = app(WorkforcePlanService::class);

        // 1. Create Plan
        $plan = $service->createPlan([
            'tenant_id' => $tenant->id,
            'code' => 'WFP-2027-ENG',
            'name' => 'Engineering FY2027 Plan',
            'planning_cycle' => 'FY2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
            'currency' => 'USD',
        ], $user->id);

        $this->assertEquals(WorkforcePlanStatus::DRAFT->value, $plan->status);
        $this->assertEquals(1, $plan->current_version);
        $this->assertCount(12, $plan->periods);
        $this->assertCount(1, $plan->versions);

        // 2. Submit Plan
        $submitted = $service->submitPlan($plan, $user->id);
        $this->assertEquals(WorkforcePlanStatus::UNDER_REVIEW->value, $submitted->status);

        // 3. Approve Plan
        $approved = $service->approvePlan($submitted, $user->id);
        $this->assertEquals(WorkforcePlanStatus::APPROVED->value, $approved->status);

        // 4. Lock Plan
        $locked = $service->lockPlan($approved, $user->id);
        $this->assertEquals(WorkforcePlanStatus::LOCKED->value, $locked->status);
        $this->assertNotNull($locked->locked_at);
        $this->assertCount(1, $locked->snapshots);

        // 5. Verify cannot submit a locked plan
        $this->expectException(ValidationException::class);
        $service->submitPlan($locked, $user->id);
    }

    public function test_plan_versioning_creates_new_version_record(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $service = app(WorkforcePlanService::class);

        $plan = $service->createPlan([
            'tenant_id' => $tenant->id,
            'code' => 'WFP-2027-SALES',
            'name' => 'Sales Plan',
            'planning_cycle' => 'FY2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
        ], $user->id);

        $service->approvePlan($plan, $user->id);
        $service->lockPlan($plan, $user->id);

        // Create Version 2
        $v2Plan = $service->createNewVersion($plan, 'Revision due to Q1 restructuring', $user->id);

        $this->assertEquals(2, $v2Plan->current_version);
        $this->assertEquals(WorkforcePlanStatus::OPEN->value, $v2Plan->status);
        $this->assertNull($v2Plan->locked_at);
        $this->assertCount(2, $v2Plan->versions);
    }
}
