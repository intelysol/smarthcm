<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceReview;
use App\Domains\Performance\Services\PerformanceReviewService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\PerformancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PerformancePermissionSeeder::class);
    }

    public function test_self_assessment_and_manager_assessment_workflow(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);
        $managerUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $empUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'user_id' => $empUser->id,
            'employee_code' => 'EMP-REV-01',
            'employee_number' => 'EMP-REV-01',
            'first_name' => 'Khurram',
            'last_name' => 'Shahzad',
            'official_email' => 'khurram@example.com',
            'employment_status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);

        $cycle = PerformanceCycle::create([
            'tenant_id' => $tenant->id,
            'name' => '2026 Appraisal Cycle',
            'cycle_type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'open',
        ]);

        $review = PerformanceReview::create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'review_type' => 'annual',
            'status' => 'draft',
            'version' => 1,
        ]);

        $service = app(PerformanceReviewService::class);

        // 1. Employee Self Assessment
        $selfAssessment = $service->submitSelfAssessment($review, [
            'achievements' => 'Delivered microservice architecture overhaul ahead of schedule.',
            'challenges' => 'Cross-functional dependency lag on legacy integration.',
            'development' => 'Desire to undertake advanced distributed systems coursework.',
            'overall_self_rating' => 4.5,
            'comments' => 'High impact delivery sprint throughout H1.',
        ], $empUser);

        $this->assertEquals('self_reviewed', $review->fresh()->status);
        $this->assertEquals(4.5, (float) $selfAssessment->overall_self_rating);

        // 2. Manager Assessment
        $managerAssessment = $service->submitManagerAssessment($review->fresh(), [
            'overall_rating' => 4.2,
            'calculated_rating' => 4.15,
            'summary' => 'Strong technical contributor who consistently demonstrates domain ownership.',
        ], $managerUser);

        $this->assertEquals('manager_reviewed', $managerAssessment->status);
        $this->assertEquals(4.2, (float) $managerAssessment->overall_rating);
        $this->assertNotNull($managerAssessment->submitted_at);
    }
}
