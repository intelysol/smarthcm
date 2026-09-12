<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Domains\Performance\Services\PerformanceAiAdvisoryService;
use App\Domains\Performance\Services\PerformanceReportingService;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceGoal;
use App\Domains\Performance\Models\PerformanceReview;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceAiAndReportingTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_advisory_is_assistive_and_marks_is_advisory_flag(): void
    {
        $ai = app(PerformanceAiAdvisoryService::class);

        // 1. SMART Goal Assistant
        $smartGoal = $ai->draftSmartGoal('We need to improve our customer support response time');
        $this->assertTrue($smartGoal['is_advisory']);
        $this->assertNotEmpty($smartGoal['suggested_objective']);
        $this->assertNotEmpty($smartGoal['suggested_key_result']);
        $this->assertEquals(2.0, $smartGoal['suggested_target']);

        // 2. Performance Summary Synthesis
        $summary = $ai->summarizePerformance(
            [
                ['progress_percentage' => 100, 'status' => 'completed'],
                ['progress_percentage' => 80, 'status' => 'active'],
                ['progress_percentage' => 40, 'status' => 'at_risk'],
            ],
            [['rating' => 4.0]],
            [['response' => 'Great team player.']]
        );

        $this->assertTrue($summary['is_advisory']);
        $this->assertEquals(3, $summary['goal_metrics']['total_goals']);
        $this->assertEquals(1, $summary['goal_metrics']['completed_goals']);
        $this->assertEquals(1, $summary['goal_metrics']['at_risk_goals']);
        $this->assertNotEmpty($summary['growth_recommendations']);
    }

    public function test_cycle_reporting_metrics(): void
    {
        $tenant = Tenant::factory()->create();
        app(\App\Domains\Platform\Contracts\TenantContext::class)->set($tenant);

        $cycle = PerformanceCycle::create([
            'tenant_id' => $tenant->id,
            'name' => '2026 Reporting Cycle',
            'cycle_type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'in_progress',
        ]);

        PerformanceReview::create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'employee_id' => '00000000-0000-0000-0000-000000000001',
            'review_type' => 'annual',
            'status' => 'manager_reviewed',
        ]);

        PerformanceGoal::create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'owner_type' => 'company',
            'title' => 'Strategic Target',
            'goal_type' => 'strategic',
            'progress_percentage' => 100,
            'status' => 'completed',
        ]);

        $reporting = app(PerformanceReportingService::class);
        $metrics = $reporting->getCycleMetrics($cycle->id, $tenant->id);

        $this->assertEquals(1, $metrics['total_reviews']);
        $this->assertEquals(1, $metrics['completed_reviews']);
        $this->assertEquals(100.0, $metrics['review_completion_rate']);
        $this->assertEquals(1, $metrics['total_goals']);
        $this->assertEquals(1, $metrics['completed_goals']);
        $this->assertEquals(100.0, $metrics['goal_completion_rate']);
    }
}
