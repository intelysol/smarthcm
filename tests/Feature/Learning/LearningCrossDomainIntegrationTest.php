<?php

namespace Tests\Feature\Learning;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningRecommendation;
use App\Domains\Learning\Services\LearningAnalyticsService;
use App\Domains\Learning\Services\LearningIntegrationService;
use App\Domains\Organization\Models\Company;
use App\Domains\Performance\Contracts\PerformanceLearningProvider;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use Database\Seeders\LearningPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningCrossDomainIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LearningPermissionSeeder::class);
    }

    public function test_performance_learning_provider_integration(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-600',
            'employee_code' => 'EMP-600',
            'first_name' => 'Naveed',
            'last_name' => 'Akhtar',
            'joining_date' => now()->toDateString(),
        ]);

        $course = LearningCourse::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'CRS-PERF-01',
            'title' => 'Strategic Thinking & Communication',
            'delivery_type' => 'self_paced',
            'duration' => 6,
        ]);

        $integrationService = app(LearningIntegrationService::class);
        $this->assertInstanceOf(PerformanceLearningProvider::class, $integrationService);

        // Create Recommendation from Competency Gap
        $rec = $integrationService->createCompetencyRecommendation(
            $employee,
            'COMP-STRAT-01',
            $course,
            'Identified gap during Q3 performance appraisal.'
        );

        $this->assertDatabaseHas('learning_recommendations', [
            'id' => $rec->id,
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'source' => 'competency_gap',
        ]);

        // Query recommended learning
        $recommended = $integrationService->recommendedLearning($employee->id, 'CYCLE-2026-Q3');
        $this->assertCount(1, $recommended);
        $this->assertEquals('Strategic Thinking & Communication', $recommended[0]['course']['title']);
    }

    public function test_analytics_snapshot_generation(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);

        $analyticsService = app(LearningAnalyticsService::class);
        $snapshot = $analyticsService->generateSnapshot($tenant->id);

        $this->assertArrayHasKey('learning_completion_rate', $snapshot);
        $this->assertArrayHasKey('mandatory_training_completion_rate', $snapshot);
        $this->assertArrayHasKey('average_course_score', $snapshot);
        $this->assertArrayHasKey('training_hours', $snapshot);
        $this->assertArrayHasKey('training_cost', $snapshot);
    }
}
