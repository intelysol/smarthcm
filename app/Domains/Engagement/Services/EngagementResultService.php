<?php

namespace App\Domains\Engagement\Services;

use App\Domains\Engagement\Contracts\SurveyResultProvider;
use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementResponse;
use App\Domains\Engagement\Models\EngagementResponseAnswer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EngagementResultService implements SurveyResultProvider
{
    public function __construct(
        protected EngagementPrivacyService $privacy
    ) {}

    public function getCampaignResults(
        EngagementCampaign $campaign,
        ?string $departmentId = null,
        ?string $locationId = null
    ): array {
        $query = EngagementResponse::query()
            ->where('campaign_id', $campaign->id)
            ->where('response_status', 'submitted');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        $totalResponses = $query->count();
        $threshold = $campaign->minimum_response_threshold ?: 5;

        // Privacy check
        if (! $this->privacy->meetsMinimumGroupThreshold($totalResponses, $threshold)) {
            return [
                'suppressed' => true,
                'reason' => "Response count ({$totalResponses}) is below the minimum threshold ({$threshold}).",
                'total_responses' => $totalResponses,
                'response_rate' => $this->calculateResponseRate($campaign),
            ];
        }

        $nps = $this->calculateNps($campaign, $departmentId, $locationId);
        $dimensionScores = $this->getDimensionScores($campaign, $departmentId, $locationId);
        $questionResults = $this->getQuestionResults($campaign, $departmentId, $locationId);
        $overallFavorability = $this->calculateOverallFavorability($campaign, $departmentId, $locationId);

        return [
            'suppressed' => false,
            'campaign_id' => $campaign->id,
            'campaign_name' => $campaign->name,
            'total_responses' => $totalResponses,
            'response_rate' => $this->calculateResponseRate($campaign),
            'overall_favorability' => $overallFavorability,
            'nps' => $nps,
            'dimension_scores' => $dimensionScores,
            'question_results' => $questionResults,
        ];
    }

    public function calculateNps(
        EngagementCampaign $campaign,
        ?string $departmentId = null,
        ?string $locationId = null
    ): array {
        $answersQuery = EngagementResponseAnswer::query()
            ->join('engagement_responses', 'engagement_response_answers.response_id', '=', 'engagement_responses.id')
            ->join('engagement_survey_questions', 'engagement_response_answers.question_id', '=', 'engagement_survey_questions.id')
            ->where('engagement_responses.campaign_id', $campaign->id)
            ->where('engagement_responses.response_status', 'submitted')
            ->where('engagement_survey_questions.question_type', 'nps');

        if ($departmentId) {
            $answersQuery->where('engagement_responses.department_id', $departmentId);
        }

        if ($locationId) {
            $answersQuery->where('engagement_responses.location_id', $locationId);
        }

        $answers = $answersQuery->select('engagement_response_answers.*')->get();
        $total = $answers->count();

        if ($total === 0) {
            return [
                'total_nps_responses' => 0,
                'promoters' => 0,
                'passives' => 0,
                'detractors' => 0,
                'promoter_percentage' => 0.0,
                'passive_percentage' => 0.0,
                'detractor_percentage' => 0.0,
                'nps_score' => 0.0,
            ];
        }

        $promoters = $answers->filter(fn ($a) => (float) $a->numeric_value >= 9)->count();
        $passives = $answers->filter(fn ($a) => (float) $a->numeric_value >= 7 && (float) $a->numeric_value <= 8)->count();
        $detractors = $answers->filter(fn ($a) => (float) $a->numeric_value <= 6)->count();

        $promoterPct = round(($promoters / $total) * 100, 2);
        $passivePct = round(($passives / $total) * 100, 2);
        $detractorPct = round(($detractors / $total) * 100, 2);
        $npsScore = round($promoterPct - $detractorPct, 2);

        return [
            'total_nps_responses' => $total,
            'promoters' => $promoters,
            'passives' => $passives,
            'detractors' => $detractors,
            'promoter_percentage' => $promoterPct,
            'passive_percentage' => $passivePct,
            'detractor_percentage' => $detractorPct,
            'nps_score' => $npsScore,
        ];
    }

    public function getDimensionScores(
        EngagementCampaign $campaign,
        ?string $departmentId = null,
        ?string $locationId = null
    ): array {
        $query = EngagementResponseAnswer::query()
            ->join('engagement_responses', 'engagement_response_answers.response_id', '=', 'engagement_responses.id')
            ->join('engagement_survey_questions', 'engagement_response_answers.question_id', '=', 'engagement_survey_questions.id')
            ->where('engagement_responses.campaign_id', $campaign->id)
            ->where('engagement_responses.response_status', 'submitted')
            ->whereNotNull('engagement_response_answers.numeric_value');

        if ($departmentId) {
            $query->where('engagement_responses.department_id', $departmentId);
        }

        if ($locationId) {
            $query->where('engagement_responses.location_id', $locationId);
        }

        $records = $query->select(
            'engagement_survey_questions.dimension',
            'engagement_response_answers.numeric_value',
            'engagement_response_answers.favorability_status'
        )->get();

        $grouped = $records->groupBy('dimension');
        $dimensions = [];

        foreach ($grouped as $dimName => $items) {
            $count = $items->count();
            $avg = round((float) $items->avg('numeric_value'), 2);
            $favorableCount = $items->where('favorability_status', 'favorable')->count();
            $favorabilityRate = $count > 0 ? round(($favorableCount / $count) * 100, 2) : 0.0;

            $dimensions[$dimName] = [
                'dimension' => $dimName,
                'sample_size' => $count,
                'average_score' => $avg,
                'favorability_rate' => $favorabilityRate,
            ];
        }

        return $dimensions;
    }

    public function calculateResponseRate(EngagementCampaign $campaign): array
    {
        $totalRecipients = $campaign->recipients()->count();
        $completedCount = $campaign->recipients()->whereNotNull('completed_at')->count();
        $startedCount = $campaign->recipients()->whereNotNull('started_at')->count();

        $rate = $totalRecipients > 0 ? round(($completedCount / $totalRecipients) * 100, 2) : 0.0;

        return [
            'total_recipients' => $totalRecipients,
            'started_count' => $startedCount,
            'completed_count' => $completedCount,
            'response_rate' => $rate,
        ];
    }

    public function calculateOverallFavorability(
        EngagementCampaign $campaign,
        ?string $departmentId = null,
        ?string $locationId = null
    ): float {
        $query = EngagementResponseAnswer::query()
            ->join('engagement_responses', 'engagement_response_answers.response_id', '=', 'engagement_responses.id')
            ->where('engagement_responses.campaign_id', $campaign->id)
            ->where('engagement_responses.response_status', 'submitted')
            ->whereNotNull('engagement_response_answers.favorability_status');

        if ($departmentId) {
            $query->where('engagement_responses.department_id', $departmentId);
        }

        if ($locationId) {
            $query->where('engagement_responses.location_id', $locationId);
        }

        $totalAnswers = $query->count();
        if ($totalAnswers === 0) {
            return 0.0;
        }

        $favorableAnswers = (clone $query)->where('engagement_response_answers.favorability_status', 'favorable')->count();

        return round(($favorableAnswers / $totalAnswers) * 100, 2);
    }

    public function getQuestionResults(
        EngagementCampaign $campaign,
        ?string $departmentId = null,
        ?string $locationId = null
    ): array {
        $questions = $campaign->survey ? $campaign->survey->questions : collect();
        $results = [];

        foreach ($questions as $q) {
            $query = EngagementResponseAnswer::query()
                ->join('engagement_responses', 'engagement_response_answers.response_id', '=', 'engagement_responses.id')
                ->where('engagement_responses.campaign_id', $campaign->id)
                ->where('engagement_responses.response_status', 'submitted')
                ->where('engagement_response_answers.question_id', $q->id);

            if ($departmentId) {
                $query->where('engagement_responses.department_id', $departmentId);
            }

            if ($locationId) {
                $query->where('engagement_responses.location_id', $locationId);
            }

            $answers = $query->select('engagement_response_answers.*')->get();
            $count = $answers->count();

            if ($count === 0) {
                continue;
            }

            $avg = $answers->avg('numeric_value');
            $favorableCount = $answers->where('favorability_status', 'favorable')->count();

            $results[] = [
                'question_id' => $q->id,
                'question' => $q->question,
                'question_type' => $q->question_type,
                'category' => $q->category,
                'dimension' => $q->dimension,
                'response_count' => $count,
                'average_score' => $avg !== null ? round((float) $avg, 2) : null,
                'favorability_rate' => $count > 0 ? round(($favorableCount / $count) * 100, 2) : null,
            ];
        }

        return $results;
    }

    public function calculateTrend(
        EngagementCampaign $currentCampaign,
        ?EngagementCampaign $previousCampaign = null
    ): array {
        $currentResults = $this->getCampaignResults($currentCampaign);

        if (! $previousCampaign) {
            return [
                'current_favorability' => $currentResults['overall_favorability'] ?? 0.0,
                'previous_favorability' => null,
                'favorability_delta' => 0.0,
                'current_nps' => $currentResults['nps']['nps_score'] ?? 0.0,
                'previous_nps' => null,
                'nps_delta' => 0.0,
            ];
        }

        $prevResults = $this->getCampaignResults($previousCampaign);

        $currFav = (float) ($currentResults['overall_favorability'] ?? 0.0);
        $prevFav = (float) ($prevResults['overall_favorability'] ?? 0.0);
        $currNps = (float) ($currentResults['nps']['nps_score'] ?? 0.0);
        $prevNps = (float) ($prevResults['nps']['nps_score'] ?? 0.0);

        return [
            'current_favorability' => $currFav,
            'previous_favorability' => $prevFav,
            'favorability_delta' => round($currFav - $prevFav, 2),
            'current_nps' => $currNps,
            'previous_nps' => $prevNps,
            'nps_delta' => round($currNps - $prevNps, 2),
        ];
    }
}
