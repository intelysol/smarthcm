<?php

namespace Tests\Feature\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\LearningPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningManagerNominationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LearningPermissionSeeder::class);
    }

    public function test_manager_can_nominate_subordinate_for_training(): void
    {
        $tenant = Tenant::factory()->create();
        $managerUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $manager = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $managerUser->id,
            'company_id' => $company->id,
            'employee_number' => 'MGR-001',
            'employee_code' => 'MGR-001',
            'first_name' => 'Sarah',
            'last_name' => 'Jenkins',
            'joining_date' => now()->toDateString(),
        ]);

        $subordinate = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'reporting_manager_id' => $manager->id,
            'employee_number' => 'EMP-300',
            'employee_code' => 'EMP-300',
            'first_name' => 'Tariq',
            'last_name' => 'Mehmood',
            'joining_date' => now()->toDateString(),
        ]);

        $course = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-LEAD-01',
            'title' => 'Executive Leadership Program',
            'delivery_type' => 'blended',
            'duration' => 20,
            'status' => 'published',
        ]);

        $response = $this->actingAs($managerUser)->postJson('/api/v1/hcm/manager/learning/nominations', [
            'employee_id' => $subordinate->id,
            'course_id' => $course->id,
            'is_mandatory' => true,
            'reason' => 'Preparation for Team Lead promotion.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('learning_nominations', [
            'employee_id' => $subordinate->id,
            'course_id' => $course->id,
            'nominated_by' => $manager->id,
            'is_mandatory' => true,
            'status' => 'pending',
        ]);
    }
}
