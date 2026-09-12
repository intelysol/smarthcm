<?php

namespace Tests\Feature\Career;

use App\Domains\Career\Enums\NineBoxPosition;
use App\Domains\Career\Models\TalentPool;
use App\Domains\Career\Models\TalentReviewRecord;
use App\Domains\Career\Models\TalentReviewSession;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\CareerPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TalentPoolAndReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CareerPermissionSeeder::class);
    }

    public function test_talent_pool_and_nine_box_calibration_workflow(): void
    {
        $tenant = Tenant::factory()->create();
        $hrUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-HIPO-01',
            'employee_code' => 'EMP-HIPO-01',
            'first_name' => 'Babar',
            'last_name' => 'Azam',
            'joining_date' => now()->toDateString(),
        ]);

        $this->grant($hrUser, [
            'hcm.talent.pool.manage', 'hcm.talent.pool.view', 'hcm.talent.view',
            'hcm.talent.review.manage', 'hcm.talent.review.view', 'hcm.talent.matrix.view',
            'hcm.talent.matrix.manage', 'hcm.talent.confidential.view'
        ]);

        // 1. Create Talent Pool & Add Member
        $poolResponse = $this->actingAs($hrUser)->postJson('/api/v1/hcm/talent/pools', [
            'code' => 'POOL-HIPO-2026',
            'name' => 'High-Potential Leaders 2026',
            'description' => 'Fast-track leadership cohort',
            'criteria' => ['min_performance' => 4.0, 'min_potential' => 4.0],
        ]);
        $poolResponse->assertStatus(201);
        $poolId = $poolResponse->json('data.id');

        $addMemberResponse = $this->actingAs($hrUser)->postJson("/api/v1/hcm/talent/pools/{$poolId}/members", [
            'employee_id' => $employee->id,
            'reason' => 'Exceptional technical leadership and delivery record.',
        ]);
        $addMemberResponse->assertStatus(201);

        $this->assertDatabaseHas('talent_pool_members', [
            'pool_id' => $poolId,
            'employee_id' => $employee->id,
        ]);

        // 2. Create Talent Review Session
        $sessionResponse = $this->actingAs($hrUser)->postJson('/api/v1/hcm/talent/reviews', [
            'title' => 'Annual Leadership Talent Review 2026',
            'scope_type' => 'organization',
            'review_date' => now()->toDateString(),
        ]);
        $sessionResponse->assertStatus(201);
        $sessionId = $sessionResponse->json('data.id');

        // 3. Record 9-Box Placement (Perf: 4.5, Pot: 4.5 -> high_performance_high_potential)
        $placementResponse = $this->actingAs($hrUser)->postJson("/api/v1/hcm/talent/reviews/{$sessionId}/placement", [
            'employee_id' => $employee->id,
            'performance_rating' => 4.5,
            'potential_rating' => 4.5,
            'readiness_level' => 'ready_now',
            'retention_risk' => 'low',
        ]);
        $placementResponse->assertStatus(201);
        $recordId = $placementResponse->json('data.id');

        $this->assertDatabaseHas('talent_review_records', [
            'id' => $recordId,
            'nine_box_position' => NineBoxPosition::HighPerfHighPot->value,
            'readiness_level' => 'ready_now',
        ]);

        // 4. Calibration Override
        $overrideResponse = $this->actingAs($hrUser)->postJson("/api/v1/hcm/talent/reviews/records/{$recordId}/override", [
            'nine_box_position' => NineBoxPosition::HighPerfMedPot->value,
            'reason' => 'Committee determined candidate requires 6 additional months in current scale before enterprise star promotion.',
        ]);
        $overrideResponse->assertStatus(200);

        $this->assertDatabaseHas('talent_review_records', [
            'id' => $recordId,
            'nine_box_position' => NineBoxPosition::HighPerfMedPot->value,
            'is_overridden' => true,
        ]);

        // 5. Query 9-Box Matrix Grid
        $matrixResponse = $this->actingAs($hrUser)->getJson('/api/v1/hcm/talent/matrix/nine-box');
        $matrixResponse->assertStatus(200);
        $this->assertEquals(1, $matrixResponse->json('total_evaluated'));
    }

    private function grant(User $user, array $permissions): void
    {
        $group = PermissionGroup::query()->firstOrCreate(['name' => 'talent'], ['label' => 'Talent']);
        foreach ($permissions as $p) {
            $perm = Permission::query()->firstOrCreate(['name' => $p], ['permission_group_id' => $group->id, 'label' => $p]);
            $user->permissions()->syncWithoutDetaching([$perm->id]);
        }
    }
}
