<?php

namespace App\Domains\Engagement\Http\Controllers;

use App\Domains\Engagement\Services\EngagementAnalyticsService;
use App\Domains\Engagement\Services\EngagementResultService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminEngagementReportController extends Controller
{
    public function __construct(
        protected EngagementAnalyticsService $analyticsService,
        protected EngagementResultService $resultService
    ) {}

    public function engagementOverview(Request $request): JsonResponse
    {
        $metrics = $this->analyticsService->generateEngagementMetrics($request->user()->tenant_id);

        return response()->json($metrics);
    }

    public function participationReport(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $campaigns = \App\Domains\Engagement\Models\EngagementCampaign::query()
            ->where('tenant_id', $tenantId)
            ->withCount(['recipients', 'responses'])
            ->get()
            ->map(function ($camp) {
                return [
                    'campaign_id' => $camp->id,
                    'name' => $camp->name,
                    'status' => $camp->status,
                    'start_date' => $camp->start_date?->toDateString(),
                    'end_date' => $camp->end_date?->toDateString(),
                    'total_recipients' => $camp->recipients_count,
                    'total_responses' => $camp->responses_count,
                    'response_rate' => $this->resultService->calculateResponseRate($camp)['response_rate'],
                ];
            });

        return response()->json($campaigns);
    }

    public function recognitionReport(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $recognitions = \App\Domains\Engagement\Models\EngagementRecognition::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->selectRaw('recognition_type, value_tag, count(*) as total, sum(likes_count) as total_likes')
            ->groupBy('recognition_type', 'value_tag')
            ->get();

        return response()->json($recognitions);
    }
}
