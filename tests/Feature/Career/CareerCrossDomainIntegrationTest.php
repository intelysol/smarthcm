<?php

namespace Tests\Feature\Career;

use App\Domains\Career\Services\CareerIntegrationService;
use App\Domains\Career\Services\CareerTalentAnalyticsService;
use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\EmployeeLearningRecord;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Organization\Models\Company;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceFinalOutcome;
use App\Domains\Performance\Models\PerformanceReview;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\CareerPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerCrossDomainIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CareerPermissionSeeder::class);
    }

    public function test_cross_domain_providers_and_analytics_ingestion(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-INT-01',
            'employee_code' => 'EMP-INT-01',
            'first_name' => 'Naseem',
            'last_name' => 'Shah',
            'joining_date' => now()->subYears(3)->toDateString(),
        ]);

        // 1. Performance Domain integration: Final outcome
        $cycle = PerformanceCycle::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'FY2026 Annual Cycle',
            'cycle_type' => 'annual',
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'status' => 'active',
        ]);

        $review = PerformanceReview::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'review_type' => 'manager_review',
            'status' => 'completed',
        ]);

        PerformanceFinalOutcome::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'final_rating' => 4.5,
            'finalized_at' => now(),
        ]);

        // 2. Learning Domain integration: Course & completed record
        $course = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-K8S-ADV',
            'title' => 'Advanced Kubernetes for Production',
            'status' => 'published',
            'credits' => 5.0,
            'duration' => 20,
        ]);

        EmployeeLearningRecord::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'completion_date' => now()->toDateString(),
            'credits_awarded' => 5.0,
            'learning_hours' => 20.0,
            'completed_at' => now(),
        ]);

        $integration = app(CareerIntegrationService::class);

        // Test Performance provider
        $perfSummary = $integration->getEmployeePerformance($employee);
        $this->assertEquals(4.5, $perfSummary['latest_rating']);

        // Test Learning provider
        $learnSummary = $integration->getEmployeeLearning($employee);
        $this->assertEquals(5.0, $learnSummary['total_credits']);
        $this->assertEquals(20.0, $learnSummary['learning_hours']);

        // Test Employee summary provider
        $empSummary = $integration->getEmployeeSummary($employee);
        $this->assertEquals('Naseem Shah', $empSummary['name']);
        $this->assertGreaterThanOrEqual(2.9, $empSummary['tenure_years']);

        // Test Analytics snapshot generation & ingestion
        $analyticsService = app(CareerTalentAnalyticsService::class);
        $snapshot = $analyticsService->generateSnapshot($tenant->id);

        $this->assertArrayHasKey('skill_coverage_rate', $snapshot);
        $this->assertArrayHasKey('succession_coverage', $snapshot);
        $this->assertArrayHasKey('development_completion_rate', $snapshot);

        // Verify ingested fact in analytics table
        $this->assertDatabaseHas('analytics_facts', [
            'tenant_id' => $tenant->id,
            'fact_type' => 'talent.succession_coverage',
        ]);
    }
}
