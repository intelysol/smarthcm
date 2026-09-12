<?php

namespace Tests\Feature\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\LearningPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningEnrollmentAndCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LearningPermissionSeeder::class);
    }

    public function test_employee_can_browse_catalog_and_self_enroll(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-100',
            'employee_code' => 'EMP-100',
            'first_name' => 'Ayesha',
            'last_name' => 'Khan',
            'joining_date' => now()->toDateString(),
        ]);

        $course = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-CAT-01',
            'title' => 'Design Thinking Principles',
            'delivery_type' => 'self_paced',
            'duration' => 4,
            'status' => 'published',
            'visibility' => 'public',
        ]);

        // 1. Browse Catalog
        $catalogResponse = $this->actingAs($user)->getJson('/api/v1/hcm/me/learning/catalog');
        $catalogResponse->assertStatus(200);
        $this->assertCount(1, $catalogResponse->json('data'));

        // 2. Self Enroll
        $enrollResponse = $this->actingAs($user)->postJson("/api/v1/hcm/me/learning/courses/{$course->id}/enroll");
        $enrollResponse->assertStatus(201);

        $this->assertDatabaseHas('learning_enrollments', [
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'enrollment_type' => 'self',
            'status' => 'enrolled',
        ]);
    }

    public function test_employee_can_withdraw_from_active_enrollment(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-101',
            'employee_code' => 'EMP-101',
            'first_name' => 'Hamza',
            'last_name' => 'Ali',
            'joining_date' => now()->toDateString(),
        ]);

        $course = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-CAT-02',
            'title' => 'Agile Scrum Foundations',
            'delivery_type' => 'self_paced',
            'duration' => 2,
            'status' => 'published',
        ]);

        $enrollment = LearningEnrollment::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'status' => 'enrolled',
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/hcm/me/learning/enrollments/{$enrollment->id}/withdraw");
        $response->assertStatus(200);

        $this->assertDatabaseHas('learning_enrollments', [
            'id' => $enrollment->id,
            'status' => 'withdrawn',
        ]);
    }
}
