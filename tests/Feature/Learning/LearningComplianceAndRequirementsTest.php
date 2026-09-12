<?php

namespace Tests\Feature\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Jobs\AssignLearningRequirementsJob;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningRequirement;
use App\Domains\Learning\Models\LearningRequirementAssignment;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\LearningPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningComplianceAndRequirementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LearningPermissionSeeder::class);
    }

    public function test_compliance_requirement_creation_and_auto_assignment_via_queue_job(): void
    {
        $tenant = Tenant::factory()->create();
        $adminUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->grant($adminUser, ['hcm.learning.requirement.create', 'hcm.learning.requirement.manage', 'hcm.learning.requirement.view']);

        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'code' => 'BU-IT',
            'name' => 'IT BU',
        ]);
        $department = Department::query()->create([
            'tenant_id' => $tenant->id,
            'business_unit_id' => $bu->id,
            'department_name' => 'Information Technology',
            'department_code' => 'IT',
        ]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $department->id,
            'employee_number' => 'EMP-400',
            'employee_code' => 'EMP-400',
            'first_name' => 'Rehan',
            'last_name' => 'Qureshi',
            'joining_date' => now()->toDateString(),
        ]);

        $course = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-SOC2',
            'title' => 'SOC 2 Type II Security Compliance',
            'delivery_type' => 'self_paced',
            'duration' => 3,
        ]);

        // 1. Admin creates requirement
        $response = $this->actingAs($adminUser)->postJson('/api/v1/hcm/learning/requirements', [
            'course_id' => $course->id,
            'title' => 'Annual SOC 2 Compliance',
            'target_type' => 'department',
            'target_id' => $department->id,
            'deadline_type' => 'relative_hiring',
            'days_offset' => 14,
            'compliance_category' => 'SOC2',
        ]);

        $response->assertStatus(201);
        $reqId = $response->json('data.id');

        // 2. Run Queue Job
        (new AssignLearningRequirementsJob($tenant->id, $employee->id, 'hired'))->handle(app(\App\Domains\Learning\Services\LearningRequirementService::class));

        // 3. Verify Assignment
        $this->assertDatabaseHas('learning_requirement_assignments', [
            'tenant_id' => $tenant->id,
            'requirement_id' => $reqId,
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'status' => 'assigned',
        ]);
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
