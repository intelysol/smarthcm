<?php

namespace App\Domains\EmployeeRelations\Http\Controllers;

use App\Domains\EmployeeRelations\Models\EmployeeRelationAllegation;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseParticipant;
use App\Domains\EmployeeRelations\Models\EmployeeRelationInterview;
use App\Domains\EmployeeRelations\Models\EmployeeRelationInvestigationQuestion;
use App\Domains\EmployeeRelations\Models\EmployeeRelationInvestigationStep;
use App\Domains\EmployeeRelations\Models\EmployeeRelationStatement;
use App\Domains\EmployeeRelations\Requests\AllegationRequest;
use App\Domains\EmployeeRelations\Requests\FindingRequest;
use App\Domains\EmployeeRelations\Requests\InterviewRequest;
use App\Domains\EmployeeRelations\Requests\InvestigationQuestionRequest;
use App\Domains\EmployeeRelations\Requests\InvestigationRequest;
use App\Domains\EmployeeRelations\Requests\InvestigationStepRequest;
use App\Domains\EmployeeRelations\Requests\StatementRequest;
use App\Domains\EmployeeRelations\Resources\EmployeeRelationFindingResource;
use App\Domains\EmployeeRelations\Resources\EmployeeRelationInterviewResource;
use App\Domains\EmployeeRelations\Resources\EmployeeRelationInvestigationResource;
use App\Domains\EmployeeRelations\Resources\EmployeeRelationStatementResource;
use App\Domains\EmployeeRelations\Services\CaseAuthorizationService;
use App\Domains\EmployeeRelations\Services\InvestigationService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvestigationController extends Controller
{
    public function __construct(
        protected InvestigationService $investigationService,
        protected CaseAuthorizationService $authService
    ) {}

    protected function authorizeCase(Request $request, EmployeeRelationCase $case): void
    {
        abort_unless($this->authService->canViewCase($request->user(), $case), 404);
    }

    public function getInvestigation(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canViewInvestigation($request->user(), $case), 403);

        $investigations = $case->investigations()->with(['investigator', 'steps', 'questions'])->get();

        return response()->json(EmployeeRelationInvestigationResource::collection($investigations));
    }

    public function startInvestigation(InvestigationRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canEditCase($request->user(), $case), 403);

        $investigator = User::query()->where('tenant_id', $case->tenant_id)->findOrFail($request->validated('investigator_id'));

        $investigation = $this->investigationService->startInvestigation(
            $case,
            $investigator,
            $request->validated('scope'),
            $request->validated('target_completion_date')
        );

        return response()->json([
            'message' => 'Investigation started.',
            'investigation' => new EmployeeRelationInvestigationResource($investigation),
        ], 201);
    }

    public function addStep(InvestigationStepRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canViewInvestigation($request->user(), $case), 403);

        $investigation = $case->investigations()->latest()->firstOrFail();

        $step = $investigation->steps()->create([
            'tenant_id' => $case->tenant_id,
            'case_id' => $case->id,
            ...$request->validated(),
        ]);

        return response()->json(['message' => 'Step added.', 'step' => $step], 201);
    }

    public function updateStep(InvestigationStepRequest $request, EmployeeRelationCase $case, EmployeeRelationInvestigationStep $step): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canViewInvestigation($request->user(), $case), 403);

        $updated = $this->investigationService->updateStep($step, $request->validated(), $request->user());

        return response()->json(['message' => 'Step updated.', 'step' => $updated]);
    }

    public function addQuestion(InvestigationQuestionRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canViewInvestigation($request->user(), $case), 403);

        $investigation = $case->investigations()->latest()->firstOrFail();

        $question = $investigation->questions()->create([
            'tenant_id' => $case->tenant_id,
            'case_id' => $case->id,
            ...$request->validated(),
        ]);

        return response()->json(['message' => 'Question added.', 'question' => $question], 201);
    }

    public function updateQuestion(Request $request, EmployeeRelationCase $case, EmployeeRelationInvestigationQuestion $question): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $request->validate(['answer' => ['required', 'string']]);

        $question->update([
            'answer' => $request->input('answer'),
            'answered_at' => now(),
        ]);

        return response()->json(['message' => 'Answer recorded.', 'question' => $question]);
    }

    public function getAllegations(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        return response()->json($case->allegations()->with(['policyReference', 'findings'])->get());
    }

    public function addAllegation(AllegationRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        abort_unless($this->authService->canEditCase($request->user(), $case), 403);

        $count = $case->allegations()->count() + 1;
        $allegationNumber = "ALG-{$case->case_number}-{$count}";

        $allegation = $case->allegations()->create([
            'tenant_id' => $case->tenant_id,
            'allegation_number' => $allegationNumber,
            ...$request->validated(),
        ]);

        return response()->json(['message' => 'Allegation recorded.', 'allegation' => $allegation], 201);
    }

    public function getStatements(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $statements = $case->statements()->with(['participant', 'versions'])->get()
            ->filter(fn ($stmt) => $this->authService->canViewStatement($request->user(), $case, $stmt))
            ->values();

        return response()->json(EmployeeRelationStatementResource::collection($statements));
    }

    public function recordStatement(StatementRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $participant = $case->participants()->findOrFail($request->validated('participant_id'));

        $statement = $this->investigationService->recordStatement(
            $case,
            $participant,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Statement recorded successfully.',
            'statement' => new EmployeeRelationStatementResource($statement),
        ], 201);
    }

    public function getInterviews(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canViewInvestigation($request->user(), $case), 403);

        $interviews = $case->interviews()->with(['participant', 'investigator'])->get();

        return response()->json(EmployeeRelationInterviewResource::collection($interviews));
    }

    public function scheduleInterview(InterviewRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canViewInvestigation($request->user(), $case), 403);

        $participant = $case->participants()->findOrFail($request->validated('participant_id'));
        $investigator = $request->filled('investigator_id')
            ? User::query()->where('tenant_id', $case->tenant_id)->findOrFail($request->validated('investigator_id'))
            : $request->user();

        $interview = $this->investigationService->scheduleInterview(
            $case,
            $participant,
            $investigator,
            $request->validated()
        );

        return response()->json([
            'message' => 'Interview scheduled.',
            'interview' => new EmployeeRelationInterviewResource($interview),
        ], 201);
    }

    public function addInterviewNotes(Request $request, EmployeeRelationCase $case, EmployeeRelationInterview $interview): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canViewInvestigation($request->user(), $case), 403);

        $request->validate(['notes' => ['required', 'string']]);

        $completed = $this->investigationService->completeInterview($interview, $request->input('notes'), $request->user());

        return response()->json([
            'message' => 'Interview completed & notes saved.',
            'interview' => new EmployeeRelationInterviewResource($completed),
        ]);
    }

    public function getFindings(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $findings = $case->findings()->with(['allegation', 'investigator'])->get();

        return response()->json(EmployeeRelationFindingResource::collection($findings));
    }

    public function recordFinding(FindingRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canViewInvestigation($request->user(), $case), 403);

        $allegation = $request->filled('allegation_id') ? $case->allegations()->find($request->validated('allegation_id')) : null;

        $finding = $this->investigationService->recordFinding(
            $case,
            $allegation,
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Finding recorded successfully.',
            'finding' => new EmployeeRelationFindingResource($finding),
        ], 201);
    }
}
