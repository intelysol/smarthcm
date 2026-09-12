<?php

namespace App\Domains\WorkforceOptimization\Services;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;

class AdvisoryWorkforceOptimizationAiService
{
    /**
     * Generate advisory explanation and strategic insight for an optimization recommendation.
     * Strictly advisory / decision support — does not execute actions or perform HR surveillance.
     */
    public function generateAdvisoryExplanation(HcmWorkforceOptimizationRecommendation $rec): array
    {
        $action = $rec->action_type;
        $cost = $rec->cost_impact;
        $capacity = $rec->capacity_impact_hours;
        $prod = $rec->productivity_impact_pct;

        $costSummary = $cost < 0 ? 'yielding expected net savings of $' . abs($cost) : 'requiring capital investment of $' . $cost;

        return [
            'executive_summary' => "Advisory insight: Implementing {$action} targets an operational yield of {$capacity} labor hours, {$costSummary} with a +{$prod}% productivity delta.",
            'strategic_trade_off_breakdown' => [
                'speed_to_impact' => match ($action) {
                    'SHIFT_REBALANCING' => 'Immediate (1-3 days)',
                    'REDEPLOY_INTERNAL' => 'Rapid (1-2 weeks)',
                    'CONTRACTOR_ENGAGE' => 'Medium (2-3 weeks)',
                    'RESKILL' => 'Medium-Long (4-6 weeks)',
                    'HIRE_PERMANENT' => 'Extended (6-12 weeks)',
                    default => 'Variable',
                },
                'talent_retention_impact' => match ($action) {
                    'RESKILL', 'REDEPLOY_INTERNAL' => 'Highly positive (fosters internal mobility)',
                    'SHIFT_REBALANCING' => 'Positive (reduces employee burnout & fatigue)',
                    'CONTRACTOR_ENGAGE' => 'Neutral',
                    default => 'Moderate',
                },
                'financial_risk' => match ($action) {
                    'CONTRACTOR_ENGAGE' => 'Medium-High (premium rates)',
                    'HIRE_PERMANENT' => 'Medium (fixed recurring cost commitment)',
                    'RESKILL' => 'Low (high ROI internal asset building)',
                    default => 'Low',
                },
            ],
            'governance_note' => 'This intelligence is advisory only. In accordance with organizational policy and algorithmic governance guardrails, HR business partner and department executive authorization is mandatory before downstream dispatch.',
        ];
    }
}
