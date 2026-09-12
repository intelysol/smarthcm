<?php

namespace App\Domains\Engagement\Services;

use App\Domains\Analytics\Services\AnalyticsService;
use App\Domains\Engagement\Contracts\EngagementAnalyticsProvider;
use App\Domains\Engagement\Models\CultureInitiative;
use App\Domains\Engagement\Models\EmployeeSuggestion;
use App\Domains\Engagement\Models\EngagementActionPlan;
use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementRecognition;
use App\Domains\Engagement\Models\EngagementResponse;
use App\Domains\Engagement\Models\EngagementResponseAnswer;

class EngagementAnalyticsService implements EngagementAnalyticsProvider
{
    public function __construct(
        protected AnalyticsService $analytics,
        protected EngagementResultService $results
    ) {}

    public function generateEngagementMetrics(string $tenantId): array
    {
        $campaigns = EngagementCampaign::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'closed'])
            ->get();

        $npsScores = [];
        $favorabilityScores = [];
        $responseRates = [];

        foreach ($campaigns as $camp) {
            $npsData = $this->results->calculateNps($camp);
            if ($npsData['total_nps_responses'] > 0) {
                $npsScores[] = (float) $npsData['nps_score'];
            }

            $fav = $this->results->calculateOverallFavorability($camp);
            if ($fav > 0) {
                $favorabilityScores[] = $fav;
            }

            $rateData = $this->results->calculateResponseRate($camp);
            if ($rateData['total_recipients'] > 0) {
                $responseRates[] = (float) $rateData['response_rate'];
            }
        }

        $avgNps = count($npsScores) > 0 ? round(array_sum($npsScores) / count($npsScores), 2) : 0.0;
        $avgFavorability = count($favorabilityScores) > 0 ? round(array_sum($favorabilityScores) / count($favorabilityScores), 2) : 0.0;
        $avgResponseRate = count($responseRates) > 0 ? round(array_sum($responseRates) / count($responseRates), 2) : 0.0;

        $totalRecognitions = EngagementRecognition::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->count();

        $totalSuggestions = EmployeeSuggestion::query()
            ->where('tenant_id', $tenantId)
            ->count();
        $implementedSuggestions = EmployeeSuggestion::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'implemented')
            ->count();
        $suggestionImplementationRate = $totalSuggestions > 0
            ? round(($implementedSuggestions / $totalSuggestions) * 100, 2)
            : 0.0;

        $totalPlans = EngagementActionPlan::query()
            ->where('tenant_id', $tenantId)
            ->count();
        $completedPlans = EngagementActionPlan::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->count();
        $actionPlanCompletionRate = $totalPlans > 0
            ? round(($completedPlans / $totalPlans) * 100, 2)
            : 0.0;

        $metrics = [
            'engagement_score' => $avgFavorability,
            'engagement_favorability' => $avgFavorability,
            'enps' => $avgNps,
            'survey_response_rate' => $avgResponseRate,
            'recognition_count' => (float) $totalRecognitions,
            'suggestion_implementation_rate' => $suggestionImplementationRate,
            'action_plan_completion_rate' => $actionPlanCompletionRate,
        ];

        $facts = [];
        foreach ($metrics as $metricKey => $val) {
            $facts[] = [
                'fact_type' => "engagement.{$metricKey}",
                'fact_date' => now()->toDateString(),
                'subject_type' => 'tenant',
                'subject_id' => $tenantId,
                'dimensions' => ['domain' => 'engagement'],
                'measures' => ['value' => (float) $val],
                'source_updated_at' => now(),
            ];
        }

        $this->analytics->ingest($tenantId, $facts);

        return $metrics;
    }
}
