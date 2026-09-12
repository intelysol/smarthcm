<?php

declare(strict_types=1);

namespace App\Domains\Performance\Services;

use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceGoal;
use App\Domains\Performance\Models\PerformanceImprovementPlan;
use App\Domains\Performance\Models\PerformanceReview;
use Illuminate\Support\Collection;

class PerformanceReportingService
{
    /**
     * Get executive cycle completion and rating distribution metrics.
     */
    public function getCycleMetrics(string $cycleId, string $tenantId): array
    {
        $cycle = PerformanceCycle::where('tenant_id', $tenantId)->findOrFail($cycleId);

        $totalReviews = PerformanceReview::where('cycle_id', $cycleId)->count();
        $completedReviews = PerformanceReview::where('cycle_id', $cycleId)
            ->whereIn('status', ['manager_reviewed', 'calibrated', 'finalized', 'published'])
            ->count();

        $reviewCompletionRate = $totalReviews > 0 ? round(($completedReviews / $totalReviews) * 100, 2) : 0.0;

        $totalGoals = PerformanceGoal::where('cycle_id', $cycleId)->count();
        $completedGoals = PerformanceGoal::where('cycle_id', $cycleId)
            ->where('progress_percentage', '>=', 100)
            ->count();

        $goalCompletionRate = $totalGoals > 0 ? round(($completedGoals / $totalGoals) * 100, 2) : 0.0;

        $activePips = PerformanceImprovementPlan::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->count();

        return [
            'cycle_id' => $cycleId,
            'cycle_name' => $cycle->name,
            'cycle_status' => $cycle->status,
            'total_reviews' => $totalReviews,
            'completed_reviews' => $completedReviews,
            'review_completion_rate' => $reviewCompletionRate,
            'total_goals' => $totalGoals,
            'completed_goals' => $completedGoals,
            'goal_completion_rate' => $goalCompletionRate,
            'active_pips' => $activePips,
        ];
    }
}
