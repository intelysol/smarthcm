<?php

namespace Tests\Unit\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningCoursePrerequisite;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Models\LearningSession;
use App\Domains\Learning\Services\LearningEligibilityService;
use App\Domains\Organization\Models\Company;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningEligibilityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_is_eligible_when_no_prerequisites_exist(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-001',
            'employee_code' => 'EMP-001',
            'first_name' => 'Sara',
            'last_name' => 'Ahmed',
            'joining_date' => now()->toDateString(),
        ]);

        $course = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-001',
            'title' => 'Introduction to Compliance',
            'delivery_type' => 'self_paced',
            'duration' => 2,
        ]);

        $service = app(LearningEligibilityService::class);
        $check = $service->isEligible($employee, $course);

        $this->assertTrue($check['eligible']);
        $this->assertEmpty($check['reasons']);
    }

    public function test_employee_is_ineligible_when_mandatory_prerequisite_is_missing(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-002',
            'employee_code' => 'EMP-002',
            'first_name' => 'Ali',
            'last_name' => 'Raza',
            'joining_date' => now()->toDateString(),
        ]);

        $prereqCourse = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-101',
            'title' => 'Basic Security 101',
            'delivery_type' => 'self_paced',
            'duration' => 1,
        ]);

        $advancedCourse = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-201',
            'title' => 'Advanced Cybersecurity',
            'delivery_type' => 'self_paced',
            'duration' => 4,
        ]);

        LearningCoursePrerequisite::query()->create([
            'tenant_id' => $tenant->id,
            'course_id' => $advancedCourse->id,
            'prerequisite_type' => 'course',
            'prerequisite_id' => $prereqCourse->id,
            'is_mandatory' => true,
        ]);

        $service = app(LearningEligibilityService::class);
        $check = $service->isEligible($employee, $advancedCourse);

        $this->assertFalse($check['eligible']);
        $this->assertStringContainsString('Missing prerequisite course', $check['reasons'][0]);

        // Complete prerequisite course
        LearningEnrollment::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'course_id' => $prereqCourse->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $checkAfter = $service->isEligible($employee, $advancedCourse);
        $this->assertTrue($checkAfter['eligible']);
    }

    public function test_session_capacity_and_conflict_checks(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $course = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-301',
            'title' => 'Live Workshop',
            'delivery_type' => 'classroom',
            'duration' => 2,
        ]);

        $session = LearningSession::query()->create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'start_datetime' => now()->addDays(2),
            'end_datetime' => now()->addDays(2)->addHours(2),
            'capacity' => 1,
            'status' => 'scheduled',
        ]);

        $service = app(LearningEligibilityService::class);
        $this->assertTrue($service->checkCapacity($session));

        // Fill session
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $emp1 = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-003',
            'employee_code' => 'EMP-003',
            'first_name' => 'Zain',
            'last_name' => 'Malik',
            'joining_date' => now()->toDateString(),
        ]);

        LearningEnrollment::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $emp1->id,
            'course_id' => $course->id,
            'session_id' => $session->id,
            'status' => 'enrolled',
        ]);

        $this->assertFalse($service->checkCapacity($session));
    }
}
