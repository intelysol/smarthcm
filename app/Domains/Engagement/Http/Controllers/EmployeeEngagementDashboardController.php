<?php

namespace App\Domains\Engagement\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementCampaignRecipient;
use App\Domains\Engagement\Models\EngagementRecognition;
use App\Domains\Engagement\Models\EngagementResponse;
use App\Domains\Engagement\Requests\RecognitionCreateRequest;
use App\Domains\Engagement\Requests\SuggestionSubmitRequest;
use App\Domains\Engagement\Requests\SurveyResponseSubmitRequest;
use App\Domains\Engagement\Resources\EmployeeSuggestionResource;
use App\Domains\Engagement\Resources\EngagementCampaignResource;
use App\Domains\Engagement\Resources\EngagementRecognitionResource;
use App\Domains\Engagement\Resources\EngagementResponseResource;
use App\Domains\Engagement\Services\EmployeeSuggestionService;
use App\Domains\Engagement\Services\EngagementRecognitionService;
use App\Domains\Engagement\Services\EngagementResponseService;
use App\Domains\Engagement\Services\EngagementTokenService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeEngagementDashboardController extends Controller
{
    public function __construct(
        protected EngagementResponseService $responseService,
        protected EngagementRecognitionService $recognitionService,
        protected EmployeeSuggestionService $suggestionService,
        protected EngagementTokenService $tokenService
    ) {}

    protected function getEmployee(Request $request): ?Employee
    {
        return Employee::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('user_id', $request->user()->id)
            ->first();
    }

    public function dashboard(Request $request): JsonResponse
    {
        $employee = $this->getEmployee($request);
        $tenantId = $request->user()->tenant_id;

        $assignedCampaignIds = $employee
            ? EngagementCampaignRecipient::query()
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employee->id)
                ->whereNull('completed_at')
                ->pluck('campaign_id')
            : collect();

        $activeSurveys = EngagementCampaign::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereIn('id', $assignedCampaignIds)
            ->with(['survey.sections.questions'])
            ->get();

        $recentRecognitions = EngagementRecognition::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->latest()
            ->limit(10)
            ->with(['sender', 'recipient'])
            ->get();

        return response()->json([
            'employee' => $employee ? ['id' => $employee->id, 'name' => "{$employee->first_name} {$employee->last_name}"] : null,
            'active_surveys' => EngagementCampaignResource::collection($activeSurveys),
            'recent_recognitions' => EngagementRecognitionResource::collection($recentRecognitions),
        ]);
    }

    public function getSurveys(Request $request): JsonResponse
    {
        $employee = $this->getEmployee($request);
        $tenantId = $request->user()->tenant_id;

        $campaigns = EngagementCampaign::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with(['survey'])
            ->get();

        return response()->json(EngagementCampaignResource::collection($campaigns));
    }

    public function getSurveyDetails(Request $request, EngagementCampaign $campaign): JsonResponse
    {
        $campaign->load(['survey.sections.questions.rulesAsSource', 'survey.questions']);

        return response()->json([
            'campaign' => new EngagementCampaignResource($campaign),
            'survey' => $campaign->survey,
            'sections' => $campaign->survey?->sections,
            'questions' => $campaign->survey?->questions,
        ]);
    }

    public function startSurvey(Request $request, EngagementCampaign $campaign): JsonResponse
    {
        $employee = $this->getEmployee($request);
        $rawToken = $request->input('raw_token');

        $response = $this->responseService->startResponse($campaign, $employee, $rawToken);

        return response()->json([
            'message' => 'Survey response session started.',
            'response' => new EngagementResponseResource($response),
        ]);
    }

    public function submitSurvey(SurveyResponseSubmitRequest $request, EngagementCampaign $campaign): JsonResponse
    {
        $employee = $this->getEmployee($request);
        $rawToken = $request->input('raw_token');
        $responseId = $request->input('response_id');

        $response = $responseId
            ? EngagementResponse::query()->where('campaign_id', $campaign->id)->findOrFail($responseId)
            : $this->responseService->startResponse($campaign, $employee, $rawToken);

        $submitted = $this->responseService->submitResponse(
            $response,
            $request->input('answers'),
            $rawToken,
            $employee
        );

        return response()->json([
            'message' => 'Survey response successfully submitted.',
            'response' => new EngagementResponseResource($submitted),
        ]);
    }

    public function getRecognition(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $recognitions = EngagementRecognition::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->latest()
            ->with(['sender', 'recipient'])
            ->paginate(20);

        return response()->json($recognitions);
    }

    public function postRecognition(RecognitionCreateRequest $request): JsonResponse
    {
        $employee = $this->getEmployee($request);
        if (! $employee) {
            return response()->json(['message' => 'Employee profile required.'], 422);
        }

        $recipient = Employee::query()->findOrFail($request->input('recipient_employee_id'));

        $recognition = $this->recognitionService->createRecognition(
            $request->user()->tenant_id,
            $employee,
            $recipient,
            $request->validated()
        );

        return response()->json([
            'message' => 'Recognition posted successfully.',
            'recognition' => new EngagementRecognitionResource($recognition),
        ], 201);
    }

    public function getSuggestions(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $suggestions = $this->suggestionService
            ? \App\Domains\Engagement\Models\EmployeeSuggestion::query()
                ->where('tenant_id', $tenantId)
                ->where('status', '!=', 'rejected')
                ->latest()
                ->with(['employee'])
                ->paginate(20)
            : collect();

        return response()->json($suggestions);
    }

    public function postSuggestion(SuggestionSubmitRequest $request): JsonResponse
    {
        $employee = $this->getEmployee($request);

        $suggestion = $this->suggestionService->submitSuggestion(
            $request->user()->tenant_id,
            $request->validated(),
            $employee
        );

        return response()->json([
            'message' => 'Suggestion submitted successfully.',
            'suggestion' => new EmployeeSuggestionResource($suggestion),
        ], 201);
    }

    public function voteSuggestion(Request $request, \App\Domains\Engagement\Models\EmployeeSuggestion $suggestion): JsonResponse
    {
        $employee = $this->getEmployee($request);
        if (! $employee) {
            return response()->json(['message' => 'Employee profile required.'], 422);
        }

        $vote = $this->suggestionService->voteSuggestion($suggestion, $employee);

        return response()->json([
            'message' => 'Vote recorded.',
            'votes_count' => $suggestion->fresh()->votes_count,
        ]);
    }

    public function getActionPlans(Request $request): JsonResponse
    {
        $employee = $this->getEmployee($request);
        $tenantId = $request->user()->tenant_id;

        $plans = \App\Domains\Engagement\Models\EngagementActionPlan::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($employee) {
                $q->where('scope_type', 'company');
                if ($employee && $employee->department_id) {
                    $q->orWhere(fn ($sub) => $sub->where('scope_type', 'department')->where('scope_id', $employee->department_id));
                }
            })
            ->with(['items'])
            ->get();

        return response()->json(\App\Domains\Engagement\Resources\EngagementActionPlanResource::collection($plans));
    }
}
