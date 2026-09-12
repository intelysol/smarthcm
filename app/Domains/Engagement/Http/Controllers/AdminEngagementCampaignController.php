<?php

namespace App\Domains\Engagement\Http\Controllers;

use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementSurvey;
use App\Domains\Engagement\Requests\CampaignCreateRequest;
use App\Domains\Engagement\Resources\EngagementCampaignResource;
use App\Domains\Engagement\Services\EngagementCampaignService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminEngagementCampaignController extends Controller
{
    public function __construct(
        protected EngagementCampaignService $campaignService
    ) {}

    protected function authorizeCampaign(Request $request, EngagementCampaign $campaign): void
    {
        abort_if($campaign->tenant_id !== $request->user()->tenant_id, 404);
    }

    public function index(Request $request): JsonResponse
    {
        $campaigns = EngagementCampaign::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['survey'])
            ->withCount(['recipients', 'responses'])
            ->latest()
            ->get();

        return response()->json(EngagementCampaignResource::collection($campaigns));
    }

    public function store(CampaignCreateRequest $request): JsonResponse
    {
        $request->validate([
            'survey_id' => ['required', 'uuid'],
        ]);

        $survey = EngagementSurvey::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($request->input('survey_id'));

        $campaign = $this->campaignService->createCampaign(
            $request->user()->tenant_id,
            $survey,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Campaign created successfully.',
            'campaign' => new EngagementCampaignResource($campaign),
        ], 201);
    }

    public function show(Request $request, EngagementCampaign $campaign): JsonResponse
    {
        $this->authorizeCampaign($request, $campaign);

        $campaign->load(['survey', 'audiences', 'schedule']);
        $campaign->loadCount(['recipients', 'responses']);

        return response()->json([
            'campaign' => new EngagementCampaignResource($campaign),
            'survey' => $campaign->survey,
            'audiences' => $campaign->audiences,
            'schedule' => $campaign->schedule,
        ]);
    }

    public function launch(Request $request, EngagementCampaign $campaign): JsonResponse
    {
        $this->authorizeCampaign($request, $campaign);

        $launched = $this->campaignService->launchCampaign($campaign, $request->user()->id);

        return response()->json([
            'message' => 'Campaign launched successfully.',
            'campaign' => new EngagementCampaignResource($launched),
        ]);
    }

    public function close(Request $request, EngagementCampaign $campaign): JsonResponse
    {
        $this->authorizeCampaign($request, $campaign);

        $closed = $this->campaignService->closeCampaign($campaign, $request->user()->id);

        return response()->json([
            'message' => 'Campaign closed successfully.',
            'campaign' => new EngagementCampaignResource($closed),
        ]);
    }

    public function sendReminders(Request $request, EngagementCampaign $campaign): JsonResponse
    {
        $this->authorizeCampaign($request, $campaign);

        $count = $this->campaignService->sendReminders($campaign);

        return response()->json([
            'message' => "Reminders dispatched to {$count} eligible recipients.",
            'reminders_sent' => $count,
        ]);
    }
}
