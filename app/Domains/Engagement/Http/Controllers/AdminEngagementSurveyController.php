<?php

namespace App\Domains\Engagement\Http\Controllers;

use App\Domains\Engagement\Models\EngagementQuestionBank;
use App\Domains\Engagement\Models\EngagementSurvey;
use App\Domains\Engagement\Models\EngagementSurveyTemplate;
use App\Domains\Engagement\Requests\SurveyCreateRequest;
use App\Domains\Engagement\Requests\SurveyQuestionRequest;
use App\Domains\Engagement\Requests\SurveyUpdateRequest;
use App\Domains\Engagement\Resources\EngagementSurveyResource;
use App\Domains\Engagement\Services\EngagementSurveyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminEngagementSurveyController extends Controller
{
    public function __construct(
        protected EngagementSurveyService $surveyService
    ) {}

    protected function authorizeSurvey(Request $request, EngagementSurvey $survey): void
    {
        abort_if($survey->tenant_id !== $request->user()->tenant_id, 404);
    }

    public function index(Request $request): JsonResponse
    {
        $surveys = EngagementSurvey::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->withCount(['sections', 'questions'])
            ->latest()
            ->get();

        return response()->json(EngagementSurveyResource::collection($surveys));
    }

    public function store(SurveyCreateRequest $request): JsonResponse
    {
        $survey = $this->surveyService->createSurvey(
            $request->user()->tenant_id,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Survey created successfully.',
            'survey' => new EngagementSurveyResource($survey),
        ], 201);
    }

    public function show(Request $request, EngagementSurvey $survey): JsonResponse
    {
        $this->authorizeSurvey($request, $survey);

        $survey->load(['sections.questions.rulesAsSource', 'questions', 'versions']);

        return response()->json([
            'survey' => new EngagementSurveyResource($survey),
            'sections' => $survey->sections,
            'questions' => $survey->questions,
            'versions' => $survey->versions,
        ]);
    }

    public function update(SurveyUpdateRequest $request, EngagementSurvey $survey): JsonResponse
    {
        $this->authorizeSurvey($request, $survey);

        $updated = $this->surveyService->updateSurvey($survey, $request->validated(), $request->user()->id);

        return response()->json([
            'message' => 'Survey updated successfully.',
            'survey' => new EngagementSurveyResource($updated),
        ]);
    }

    public function publish(Request $request, EngagementSurvey $survey): JsonResponse
    {
        $this->authorizeSurvey($request, $survey);

        $version = $this->surveyService->publishSurvey($survey, $request->user()->id);

        return response()->json([
            'message' => 'Survey published successfully.',
            'version' => $version,
        ]);
    }

    public function clone(Request $request, EngagementSurvey $survey): JsonResponse
    {
        $this->authorizeSurvey($request, $survey);

        $request->validate([
            'new_code' => ['required', 'string', 'max:50'],
            'new_title' => ['required', 'string', 'max:255'],
        ]);

        $cloned = $this->surveyService->cloneSurvey(
            $survey,
            $request->input('new_code'),
            $request->input('new_title'),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Survey cloned successfully.',
            'survey' => new EngagementSurveyResource($cloned),
        ], 201);
    }

    public function addQuestion(SurveyQuestionRequest $request, EngagementSurvey $survey): JsonResponse
    {
        $this->authorizeSurvey($request, $survey);

        $question = $this->surveyService->addQuestion($survey, $request->validated());

        return response()->json([
            'message' => 'Question added successfully.',
            'question' => $question,
        ], 201);
    }

    public function addSection(Request $request, EngagementSurvey $survey): JsonResponse
    {
        $this->authorizeSurvey($request, $survey);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $section = $this->surveyService->addSection(
            $survey,
            $request->input('title'),
            $request->input('description'),
            $request->input('sort_order', 0)
        );

        return response()->json([
            'message' => 'Section added successfully.',
            'section' => $section,
        ], 201);
    }

    public function getQuestionBank(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $bank = EngagementQuestionBank::query()
            ->where(fn ($q) => $q->where('tenant_id', $tenantId)->orWhere('is_system', true))
            ->where('status', 'active')
            ->get();

        return response()->json($bank);
    }

    public function getTemplates(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $templates = EngagementSurveyTemplate::query()
            ->where(fn ($q) => $q->where('tenant_id', $tenantId)->orWhere('is_system', true))
            ->get();

        return response()->json($templates);
    }
}
