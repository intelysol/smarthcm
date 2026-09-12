<?php

namespace Tests\Unit\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\EmployeeLearningRecord;
use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Learning\Models\LearningItem;
use App\Domains\Learning\Models\LearningProgress;
use App\Domains\Learning\Services\LearningCompletionService;
use App\Domains\Organization\Models\Company;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningCompletionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_completion_issues_certificate_and_updates_transcript(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-020',
            'employee_code' => 'EMP-020',
            'first_name' => 'Bilal',
            'last_name' => 'Hassan',
            'joining_date' => now()->toDateString(),
        ]);

        $course = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-500',
            'title' => 'Anti-Money Laundering (AML)',
            'delivery_type' => 'self_paced',
            'duration' => 3,
            'credit_points' => 5,
            'passing_score' => 80.0,
        ]);

        $item = LearningItem::query()->create([
            'tenant_id' => $tenant->id,
            'course_id' => $course->id,
            'item_type' => 'video',
            'title' => 'AML Policy Overview',
            'is_mandatory' => true,
        ]);

        $enrollment = LearningEnrollment::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'status' => 'in_progress',
            'score' => 90.0, // Passed score
            'passed' => true,
        ]);

        // Complete mandatory item
        LearningProgress::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'enrollment_id' => $enrollment->id,
            'learning_item_id' => $item->id,
            'status' => 'completed',
            'progress_percentage' => 100.0,
        ]);

        $service = app(LearningCompletionService::class);
        $completed = $service->evaluateCourseCompletion($enrollment);

        $this->assertTrue($completed);
        $this->assertDatabaseHas('learning_enrollments', [
            'id' => $enrollment->id,
            'status' => 'completed',
            'progress_percentage' => 100.0,
        ]);

        $this->assertDatabaseHas('learning_certificates', [
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('employee_learning_records', [
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'credits_awarded' => 5.0,
        ]);

        $this->assertDatabaseHas('learning_credits', [
            'employee_id' => $employee->id,
            'total_earned' => 5.0,
        ]);
    }
}
