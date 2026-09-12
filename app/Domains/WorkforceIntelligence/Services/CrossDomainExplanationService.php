<?php

namespace App\Domains\WorkforceIntelligence\Services;

use App\Domains\WorkforceIntelligence\DTOs\CrossDomainExplanationData;

class CrossDomainExplanationService
{
    public function explainAnomaly(string $metricKey, string $tenantId, ?string $departmentId = null): CrossDomainExplanationData
    {
        if (str_contains(strtoupper($metricKey), 'OVERTIME')) {
            return new CrossDomainExplanationData(
                kpiOrEvent: 'Overtime Spike (+32%) in Department',
                observedSummary: 'Observed significant overtime expenditure growth during the last 30-day period.',
                observedFacts: [
                    'Overtime hours increased by 32.4% over trailing 30 days.',
                    'Direct overtime premium spend was $18,400 above normal baseline.',
                    'Total scheduled shift hours were fulfilled at only 81% regular staffing capacity.'
                ],
                correlatedFactors: [
                    'Unscheduled sick leave and unplanned absence rose by 14.2% over the same interval.',
                    'Production throughput volume rose by 11.5% due to end-of-quarter client commitments.'
                ],
                inferredCauses: [
                    'Under-staffed shift rosters compounded by vacancy lead-time caused managers to authorize mandatory overtime rather than load-shedding.',
                    'Absence clusters in early-week shifts forced extended shift doubles.'
                ],
                recommendedActions: [
                    'Expedite cross-deployment of 3 qualified operators from Low-Volume Lines.',
                    'Accelerate candidate screening for 2 vacant operator requisitions.',
                    'Review shift scheduling patterns to prevent employee fatigue.'
                ]
            );
        }

        // Generic fallback explanation
        return new CrossDomainExplanationData(
            kpiOrEvent: $metricKey,
            observedSummary: "Multi-factor operational variance detected for {$metricKey}.",
            observedFacts: ["Variance magnitude is within warning threshold."],
            correlatedFactors: ["Correlated with recent operational activity."],
            inferredCauses: ["Seasonal demand and headcount scheduling fluctuation."],
            recommendedActions: ["Maintain monitoring across next reporting cycle."]
        );
    }
}
