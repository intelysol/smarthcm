<?php

namespace Tests\Feature\Learning;

use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\LearningPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningTenantSecurityAndRBACTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LearningPermissionSeeder::class);
    }

    public function test_tenant_data_isolation_on_courses(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
        $this->grant($userA, ['hcm.learning.course.view', 'hcm.learning.view']);

        $userB = User::factory()->create(['tenant_id' => $tenantB->id]);
        $this->grant($userB, ['hcm.learning.course.view', 'hcm.learning.view']);

        $courseA = LearningCourse::query()->create([
            'tenant_id' => $tenantA->id,
            'code' => 'CRS-TEN-A',
            'title' => 'Tenant A Proprietary Training',
            'delivery_type' => 'self_paced',
            'duration' => 2,
            'status' => 'published',
        ]);

        $courseB = LearningCourse::query()->create([
            'tenant_id' => $tenantB->id,
            'code' => 'CRS-TEN-B',
            'title' => 'Tenant B Confidential Guidelines',
            'delivery_type' => 'self_paced',
            'duration' => 2,
            'status' => 'published',
        ]);

        // User A should only see Course A
        $responseA = $this->actingAs($userA)->getJson('/api/v1/hcm/learning/courses');
        $responseA->assertStatus(200);
        $this->assertCount(1, $responseA->json('data'));
        $this->assertEquals('CRS-TEN-A', $responseA->json('data.0.code'));

        // User A cannot view Course B directly
        $responseForbidden = $this->actingAs($userA)->getJson("/api/v1/hcm/learning/courses/{$courseB->id}");
        $responseForbidden->assertStatus(403);
    }

    public function test_rbac_unauthorized_user_cannot_create_course(): void
    {
        $tenant = Tenant::factory()->create();
        $unauthorizedUser = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($unauthorizedUser)->postJson('/api/v1/hcm/learning/courses', [
            'code' => 'CRS-HACK',
            'title' => 'Unauthorized Course Attempt',
            'delivery_type' => 'self_paced',
            'duration' => 1,
        ]);

        $response->assertStatus(403);
    }

    private function grant(User $user, array $permissions): void
    {
        $group = PermissionGroup::query()->firstOrCreate(['name' => 'learning'], ['label' => 'Learning']);
        foreach ($permissions as $p) {
            $perm = Permission::query()->firstOrCreate(['name' => $p], ['permission_group_id' => $group->id, 'label' => $p]);
            $user->permissions()->syncWithoutDetaching([$perm->id]);
        }
    }
}
