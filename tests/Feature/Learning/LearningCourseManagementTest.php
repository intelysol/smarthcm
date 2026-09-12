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

class LearningCourseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LearningPermissionSeeder::class);
    }

    public function test_admin_can_create_and_publish_course(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->grant($user, ['hcm.learning.course.create', 'hcm.learning.course.edit', 'hcm.learning.course.publish', 'hcm.learning.course.view']);

        $response = $this->actingAs($user)->postJson('/api/v1/hcm/learning/courses', [
            'code' => 'CRS-DEV-01',
            'title' => 'Secure Coding in PHP & Laravel',
            'description' => 'Comprehensive guide to building resilient enterprise software.',
            'delivery_type' => 'self_paced',
            'difficulty' => 'intermediate',
            'duration' => 8,
            'duration_unit' => 'hours',
            'credit_points' => 10,
            'passing_score' => 80.0,
        ]);

        $response->assertStatus(201);
        $courseId = $response->json('data.id');

        $this->assertDatabaseHas('learning_courses', [
            'id' => $courseId,
            'code' => 'CRS-DEV-01',
            'status' => 'draft',
            'current_version' => 1,
        ]);

        // Publish course
        $publishResponse = $this->actingAs($user)->postJson("/api/v1/hcm/learning/courses/{$courseId}/publish");
        $publishResponse->assertStatus(200);

        $this->assertDatabaseHas('learning_courses', [
            'id' => $courseId,
            'status' => 'published',
        ]);
    }

    public function test_course_versioning_creates_snapshot_and_increments_version(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->grant($user, ['hcm.learning.course.create', 'hcm.learning.course.edit', 'hcm.learning.course.view']);

        $course = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-VER-01',
            'title' => 'Cloud Architecture v1',
            'delivery_type' => 'self_paced',
            'duration' => 5,
            'current_version' => 1,
            'status' => 'published',
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/hcm/learning/courses/{$course->id}/version", [
            'change_log' => 'Added AWS and GCP microservices modules.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('learning_course_versions', [
            'course_id' => $course->id,
            'version_number' => 2,
            'change_log' => 'Added AWS and GCP microservices modules.',
        ]);

        $this->assertEquals(2, $course->fresh()->current_version);
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
