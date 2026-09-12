<?php

namespace App\Domains\Performance\Services;

use App\Domains\Performance\Models\PerformanceCycleConfiguration;
use App\Domains\Performance\Models\PerformanceRatingScale;
use Illuminate\Validation\ValidationException;

final class PerformanceRatingEngine
{
    /** @param iterable<object{progress_percentage:mixed,weight:mixed}> $goals */
    public function calculateGoalScore(iterable $goals): float
    {
        $weighted = 0.0; $weight = 0.0;
        foreach ($goals as $goal) { $weighted += (float) $goal->progress_percentage * (float) $goal->weight; $weight += (float) $goal->weight; }
        if ($weight <= 0) { return 0.0; }
        return round($weighted / $weight, 4);
    }

    /** @param iterable<object{rating:mixed}> $assessments */
    public function calculateCompetencyScore(iterable $assessments): float
    {
        $ratings = collect($assessments)->pluck('rating')->map(fn ($value): float => (float) $value);
        return round((float) ($ratings->avg() ?? 0), 4);
    }

    /** @param array<string, float|int|null> $components */
    public function calculateOverallScore(PerformanceCycleConfiguration $configuration, array $components): float
    {
        $weights = ['goal' => (float) $configuration->goal_weight, 'competency' => (float) $configuration->competency_weight, 'feedback' => (float) $configuration->feedback_weight];
        $total = array_sum($weights);
        if (abs($total - 100.0) > 0.01) { throw ValidationException::withMessages(['weights' => 'Configured performance component weights must total 100%.']); }
        $score = 0.0;
        foreach ($weights as $component => $weight) { $score += ((float) ($components[$component] ?? 0)) * ($weight / 100); }
        return round($score, 4);
    }

    public function calculateFinalRating(PerformanceRatingScale $scale, float $score): float
    {
        $item = $scale->items()->where(fn ($query) => $query->whereNull('min_score')->orWhere('min_score', '<=', $score))->where(fn ($query) => $query->whereNull('max_score')->orWhere('max_score', '>=', $score))->orderByDesc('value')->first();
        if ($item === null) { throw ValidationException::withMessages(['rating_scale' => 'No rating-scale item matches the calculated score.']); }
        return (float) $item->value;
    }
}
