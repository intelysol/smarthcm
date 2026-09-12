<?php

namespace App\Domains\EmployeeRelations\Http\Controllers;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCorrectiveAction;
use App\Domains\EmployeeRelations\Models\EmployeeRelationDecision;
use App\Domains\EmployeeRelations\Requests\ActionAcknowledgeRequest;
use App\Domains\EmployeeRelations\Requests\CorrectiveActionRequest;
use App\Domains\EmployeeRelations\Requests\DecisionRequest;
use App\Domains\EmployeeRelations\Resources\EmployeeRelationCorrectiveActionResource;
use App\Domains\EmployeeRelations\Resources\EmployeeRelationDecisionResource;
use App\Domains\EmployeeRelations\Services\CaseAuthorizationService;
use App\Domains\EmployeeRelations\Services\DecisionAndAppealService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DecisionController extends Controller
{
    public function __construct(
        protected DecisionAndAppealService $decisionService,
        protected CaseAuthorizationService $authService
    ) {}

    protected function authorizeCase(Request $request, EmployeeRelationCase $case): void
    {
        abort_unless($this->authService->canViewCase($request->user(), $case), 404);
    }

    public function getDecision(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $decisions = $case->decisions()->with(['decisionMaker', 'correctiveActions.assignedEmployee'])->get();

        return response()->json(EmployeeRelationDecisionResource::collection($decisions));
    }

    public function recordDecision(DecisionRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canMakeDecision($request->user(), $case), 403);

        $decision = $this->decisionService->recordDecision(
            $case,
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Formal case decision recorded successfully.',
            'decision' => new EmployeeRelationDecisionResource($decision),
        ], 201);
    }

    public function getActions(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $actions = $case->correctiveActions()->with('assignedEmployee')->get();

        return response()->json(EmployeeRelationCorrectiveActionResource::collection($actions));
    }

    public function addAction(CorrectiveActionRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canEditCase($request->user(), $case), 403);

        $decision = $request->filled('decision_id') ? $case->decisions()->find($request->validated('decision_id')) : null;

        $action = $this->decisionService->createCorrectiveAction(
            $case,
            $decision,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Corrective action created.',
            'action' => new EmployeeRelationCorrectiveActionResource($action),
        ], 201);
    }

    public function acknowledgeAction(ActionAcknowledgeRequest $request, EmployeeRelationCase $case, EmployeeRelationCorrectiveAction $action): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $acknowledged = $this->decisionService->acknowledgeAction(
            $action,
            $request->validated('status'),
            $request->validated('comment')
        );

        return response()->json([
            'message' => 'Corrective action acknowledgement recorded.',
            'action' => new EmployeeRelationCorrectiveActionResource($acknowledged),
        ]);
    }

    public function completeAction(Request $request, EmployeeRelationCase $case, EmployeeRelationCorrectiveAction $action): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $completed = $this->decisionService->completeAction($action);

        return response()->json([
            'message' => 'Corrective action marked as completed.',
            'action' => new EmployeeRelationCorrectiveActionResource($completed),
        ]);
    }

    public function verifyAction(Request $request, EmployeeRelationCase $case, EmployeeRelationCorrectiveAction $action): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canEditCase($request->user(), $case), 403);

        $verified = $this->decisionService->verifyAction($action, $request->user());

        return response()->json([
            'message' => 'Corrective action verified.',
            'action' => new EmployeeRelationCorrectiveActionResource($verified),
        ]);
    }
}
