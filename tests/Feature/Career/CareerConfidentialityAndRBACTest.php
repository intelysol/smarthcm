<?php

namespace Tests\Feature\Career;

use App\Domains\Career\Models\CareerPlan;
use App\Domains\Career\Models\CareerSkill;
use App\Domains\Career\Models\SuccessionPlan;
use App\Domains\Career\Models\TalentPool;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\CareerPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerConfidentialityAndRBACTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CareerPermissionSeeder::class);
    }

    public function test_tenant_isolation_and_confidentiality_boundaries(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);

        $this->grant($userA, ['hcm.talent.pool.view', 'hcm.talent.view', 'hcm.career.skills.view', 'hcm.career.view']);
        $this->grant($userB, ['hcm.talent.pool.view', 'hcm.talent.view', 'hcm.career.skills.view', 'hcm.career.view']);

        // Create Tenant A data
        $poolA = TalentPool::query()->create([
            'tenant_id' => $tenantA->id,
            'code' => 'POOL-A-LEAD',
            'name' => 'Tenant A Leadership Cohort',
            'status' => 'active',
        ]);

        $skillA = CareerSkill::query()->create([
            'tenant_id' => $tenantA->id,
            'code' => 'SK-A-DEVOPS',
            'name' => 'Tenant A DevOps Mastery',
            'skill_type' => 'technical',
            'status' => 'active',
        ]);

        // Create Tenant B data
        $poolB = TalentPool::query()->create([
            'tenant_id' => $tenantB->id,
            'code' => 'POOL-B-LEAD',
            'name' => 'Tenant B Leadership Cohort',
            'status' => 'active',
        ]);

        // User A queries talent pools
        $responseA = $this->actingAs($userA)->getJson('/api/v1/hcm/talent/pools');
        $responseA->assertStatus(200);
        $poolNames = collect($responseA->json('data') ?? $responseA->json())->pluck('name');

        $this->assertTrue($poolNames->contains('Tenant A Leadership Cohort'));
        $this->assertFalse($poolNames->contains('Tenant B Leadership Cohort'));
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
