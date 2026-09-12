<?php

namespace App\Domains\EmployeeRelations\Http\Controllers;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Models\EmployeeRelationHearing;
use App\Domains\EmployeeRelations\Requests\HearingOutcomeRequest;
use App\Domains\EmployeeRelations\Requests\HearingRequest;
use App\Domains\EmployeeRelations\Resources\EmployeeRelationHearingResource;
use App\Domains\EmployeeRelations\Services\CaseAuthorizationService;
use App\Domains\EmployeeRelations\Services\DecisionAndAppealService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HearingController extends Controller
{
    public function __construct(
        protected DecisionAndAppealService $hearingService,
        protected CaseAuthorizationService $authService
    ) {}

    protected function authorizeCase(Request $request, EmployeeRelationCase $case): void
    {
        abort_unless($this->authService->canViewCase($request->user(), $case), 404);
    }

    public function index(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $hearings = $case->hearings()->with(['chairperson', 'outcome'])->get();

        return response()->json(EmployeeRelationHearingResource::collection($hearings));
    }

    public function store(HearingRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canEditCase($request->user(), $case), 403);

        $chairperson = $request->filled('chairperson_id')
            ? User::query()->where('tenant_id', $case->tenant_id)->findOrFail($request->validated('chairperson_id'))
            : $request->user();

        $hearing = $this->hearingService->scheduleHearing($case, $chairperson, $request->validated());

        return response()->json([
            'message' => 'Hearing scheduled successfully.',
            'hearing' => new EmployeeRelationHearingResource($hearing),
        ], 201);
    }

    public function recordOutcome(HearingOutcomeRequest $request, EmployeeRelationCase $case, EmployeeRelationHearing $hearing): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canEditCase($request->user(), $case) || $hearing->chairperson_id === $request->user()->id, 403);

        $outcome = $this->hearingService->recordHearingOutcome($hearing, $request->user(), $request->validated());

        return response()->json([
            'message' => 'Hearing outcome recorded.',
            'outcome' => $outcome,
        ], 201);
    }
}
