<?php

namespace App\Domains\Engagement\Http\Controllers;

use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementTextAnalysis;
use App\Domains\Engagement\Services\EngagementPrivacyService;
use App\Domains\Engagement\Services\EngagementResultService;
use App\Domains\Engagement\Services\EngagementTextAnalysisService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminEngagementResultController extends Controller
{
    public function __construct(
        protected EngagementResultService $resultService,
        protected EngagementPrivacyService $privacyService,
        protected EngagementTextAnalysisService $textAnalysisService
    ) {}

    protected function authorizeCampaign(Request $request, EngagementCampaign $campaign): void
    {
        abort_if($campaign->tenant_id !== $request->user()->tenant_id, 404);
    }

    public function getResults(Request $request, EngagementCampaign $campaign): JsonResponse
    {
        $this->authorizeCampaign($request, $campaign);

        $departmentId = $request->query('department_id');
        $locationId = $request->query('location_id');

        $results = $this->resultService->getCampaignResults($campaign, $departmentId, $locationId);

        return response()->json($results);
    }

    public function getNps(Request $request, EngagementCampaign $campaign): JsonResponse
    {
        $this->authorizeCampaign($request, $campaign);

        $departmentId = $request->query('department_id');
        $locationId = $request->query('location_id');

        $nps = $this->resultService->calculateNps($campaign, $departmentId, $locationId);

        return response()->json($nps);
    }

    public function getDimensionScores(Request $request, EngagementCampaign $campaign): JsonResponse
    {
        $this->authorizeCampaign($request, $campaign);

        $departmentId = $request->query('department_id');
        $locationId = $request->query('location_id');

        $scores = $this->resultService->getDimensionScores($campaign, $departmentId, $locationId);

        return response()->json($scores);
    }

    public function getTrends(Request $request, EngagementCampaign $campaign): JsonResponse
    {
        $this->authorizeCampaign($request, $campaign);

        $prevCampaignId = $request->query('previous_campaign_id');
        $prevCampaign = $prevCampaignId ? EngagementCampaign::query()->find($prevCampaignId) : null;

        $trend = $this->resultService->calculateTrend($campaign, $prevCampaign);

        return response()->json($trend);
    }

    public function getTextAnalysis(Request $request, EngagementCampaign $campaign): JsonResponse
    {
        $this->authorizeCampaign($request, $campaign);

        $analysis = EngagementTextAnalysis::query()
            ->where('campaign_id', $campaign->id)
            ->get();

        if ($analysis->isEmpty()) {
            $analysis = $this->textAnalysisService->analyzeCampaignComments($campaign);
        }

        return response()->json($analysis);
    }
}
