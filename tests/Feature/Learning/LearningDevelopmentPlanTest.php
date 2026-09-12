<?php

namespace Tests\Feature\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Services\LearningDevelopmentPlanService;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningDevelopmentPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_individual_development_plan_lifecycle_and_activities(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $bu = BusinessUnit::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'HQ Unit', 'code' => 'BU-01']);
        $dept = Department::create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'business_unit_id' => $bu->id, 'department_name' => 'Tech', 'department_code' => 'TECH']);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'employee_code' => 'EMP-IDP-01',
            'employee_number' => 'EMP-IDP-01',
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'official_email' => 'bob.j@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $course = LearningCourse::create([
            'tenant_id' => $tenant->id,
            'title' => 'Kubernetes Administration',
            'code' => 'K8S-01',
            'delivery_type' => 'online',
            'status' => 'published',
            'duration_minutes' => 480,
        ]);

        $service = app(LearningDevelopmentPlanService::class);

        // 1. Create IDP with Activities
        $plan = $service->createPlan([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'title' => 'DevOps Transition Plan',
            'goal' => 'Transition from Junior Sysadmin to Cloud DevOps Engineer',
            'skill_target' => 'Kubernetes',
            'target_level' => 'advanced',
            'target_completion_date' => '2026-12-31',
            'activities' => [
                [
                    'activity_type' => 'course',
                    'title' => 'Complete Kubernetes Course',
                    'course_id' => $course->id,
                    'target_date' => '2026-06-30',
                ],
                [
                    'activity_type' => 'mentoring',
                    'title' => 'Weekly Mentoring with Principal SRE',
                    'target_date' => '2026-09-30',
                ],
            ],
        ]);

        $this->assertEquals('active', $plan->status);
        $this->assertCount(2, $plan->activities);

        // 2. Complete First Activity
        $activity1 = $plan->activities()->first();
        $service->completeActivity($activity1, 'Successfully passed course test');
        $this->assertEquals('completed', $activity1->fresh()->status);
        $this->assertEquals('active', $plan->fresh()->status); // Plan still active since activity 2 is pending

        // 3. Complete Second Activity -> Auto Complete Plan
        $activity2 = $plan->activities()->where('activity_type', 'mentoring')->first();
        $service->completeActivity($activity2, 'Finished 12 weeks mentoring cycle');
        $this->assertEquals('completed', $activity2->fresh()->status);
        $this->assertEquals('completed', $plan->fresh()->status);
        $this->assertNotNull($plan->fresh()->completed_at);
    }
}
