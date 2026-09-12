<?php

namespace App\Domains\WorkforceOptimization\Services;

use App\Domains\WorkforceOptimization\DTOs\RealizedOutcomeData;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationAction;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationOutcome;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;

class OptimizationOutcomeService
{
    /**
     * Measure realized outcome of an implemented recommendation / action.
     */
    public function measureOutcome(
        HcmWorkforceOptimizationRecommendation $recommendation,
        string $metricCategory,
        string $metricName,
        float $baselineValue,
        float $actualValue,
        int $windowDays = 30
    ): HcmWorkforceOptimizationOutcome {
        $predictedValue = match (strtoupper($metricCategory)) {
            'COST' => $baselineValue + (float) $recommendation->cost_impact,
            'CAPACITY' => $baselineValue + (float) $recommendation->capacity_impact_hours,
            'PRODUCTIVITY' => $baselineValue * (1 + ((float) $recommendation->productivity_impact_pct / 100.0)),
            default => $baselineValue,
        };

        $variance = $actualValue - $predictedValue;
        $variancePct = $predictedValue != 0 ? round(($variance / abs($predictedValue)) * 100, 2) : 0.0;

        $status = abs($variancePct) <= 15.0 ? 'ACHIEVED' : ($variance > 0 ? 'EXCEEDED' : 'UNDERPERFORMED');

        return HcmWorkforceOptimizationOutcome::create([
            'tenant_id' => $recommendation->tenant_id,
            'recommendation_id' => $recommendation->id,
            'action_id' => $recommendation->actions()->first()?->id,
            'measurement_date' => now()->toDateString(),
            'metric_name' => $metricName,
            'pre_action_value' => $baselineValue,
            'post_action_value' => round($actualValue, 2),
            'variance_value' => round($variance, 2),
            'variance_pct' => $variancePct,
            'realized_financial_impact' => round($actualValue - $baselineValue, 2),
            'causality_label' => 'CAUSAL',
            'notes' => "Measured at day {$windowDays}: variance is {$variancePct}%. Status: {$status}.",
        ]);
    }
}
