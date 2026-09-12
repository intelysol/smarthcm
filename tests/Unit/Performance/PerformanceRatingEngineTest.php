<?php

namespace Tests\Unit\Performance;

use App\Domains\Performance\Models\PerformanceCycleConfiguration;
use App\Domains\Performance\Services\PerformanceRatingEngine;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PerformanceRatingEngineTest extends TestCase
{
    public function test_it_calculates_weighted_goal_and_component_scores(): void
    {
        $engine = new PerformanceRatingEngine();
        $goalScore = $engine->calculateGoalScore([(object) ['progress_percentage' => 80, 'weight' => 40], (object) ['progress_percentage' => 60, 'weight' => 60]]);
        $configuration = new PerformanceCycleConfiguration(['goal_weight' => 70, 'competency_weight' => 30, 'feedback_weight' => 0]);

        self::assertSame(68.0, $goalScore);
        self::assertSame(71.6, $engine->calculateOverallScore($configuration, ['goal' => $goalScore, 'competency' => 80, 'feedback' => 0]));
    }

    public function test_it_rejects_invalid_component_weights(): void
    {
        $this->expectException(ValidationException::class);
        (new PerformanceRatingEngine())->calculateOverallScore(new PerformanceCycleConfiguration(['goal_weight' => 50, 'competency_weight' => 20, 'feedback_weight' => 0]), ['goal' => 80, 'competency' => 80]);
    }
}
