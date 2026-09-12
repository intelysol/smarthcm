<?php

namespace App\Domains\WorkforceCost\Services;

use App\Domains\WorkforceCost\Models\HcmWorkforceCostLine;
use Carbon\Carbon;

class AdvisoryWorkforceCostAiService
{
    /**
     * Generate advisory workforce cost insights, anomaly detections, and recommendations.
     */
    public function generateCostInsights(string $tenantId, Carbon $start, Carbon $end): array
    {
        $lines = HcmWorkforceCostLine::where('tenant_id', $tenantId)
            ->whereDate('cost_date', '>=', $start->toDateString())
            ->whereDate('cost_date', '<=', $end->toDateString())
            ->get();

        $totalCost = (float) $lines->sum('amount');
        $otCost = (float) $lines->where('component_type', 'OVERTIME')->sum('amount');
        $contractorCost = (float) $lines->where('cost_category', 'contractor')->sum('amount');

        $anomalies = [];
        if ($totalCost > 0 && ($otCost / $totalCost) > 0.15) {
            $anomalies[] = [
                'type' => 'elevated_overtime',
                'severity' => 'warning',
                'message' => 'Overtime cost exceeds 15% of total labor cost for the selected period.',
            ];
        }

        if ($totalCost > 0 && ($contractorCost / $totalCost) > 0.25) {
            $anomalies[] = [
                'type' => 'high_contractor_spend',
                'severity' => 'advisory',
                'message' => 'Contractor spend represents over 25% of total workforce cost.',
            ];
        }

        return [
            'is_advisory_only' => true,
            'autonomous_actions_permitted' => false,
            'analysis_period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'metrics_summary' => [
                'total_cost' => $totalCost,
                'overtime_cost' => $otCost,
                'contractor_cost' => $contractorCost,
            ],
            'anomalies_detected' => $anomalies,
            'recommendations' => [
                'Evaluate shift roster distribution to reduce peak overtime premiums.',
                'Analyze contractor roles for potential full-time employee conversion to reduce long-term hourly spend.',
            ],
            'disclaimer' => 'All AI cost insights are advisory and require human authorization before any management or financial action.',
        ];
    }
}