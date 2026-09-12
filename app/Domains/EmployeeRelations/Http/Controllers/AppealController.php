<?php

namespace App\Domains\EmployeeRelations\Http\Controllers;

use App\Domains\EmployeeRelations\Models\EmployeeRelationAppeal;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\EmployeeRelations\Requests\AppealRequest;
use App\Domains\EmployeeRelations\Requests\AppealResolveRequest;
use App\Domains\EmployeeRelations\Resources\EmployeeRelationAppealResource;
use App\Domains\EmployeeRelations\Services\CaseAuthorizationService;
use App\Domains\EmployeeRelations\Services\DecisionAndAppealService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppealController extends Controller
{
    public function __construct(
        protected DecisionAndAppealService $appealService,
        protected CaseAuthorizationService $authService
    ) {}

    protected function authorizeCase(Request $request, EmployeeRelationCase $case): void
    {
        abort_unless($this->authService->canViewCase($request->user(), $case), 404);
    }

    public function index(Request $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $appeals = $case->appeals()->with(['submitter', 'reviewer', 'resolver'])->get();

        return response()->json(EmployeeRelationAppealResource::collection($appeals));
    }

    public function store(AppealRequest $request, EmployeeRelationCase $case): JsonResponse
    {
        $this->authorizeCase($request, $case);

        $appeal = $this->appealService->submitAppeal($case, $request->user(), $request->validated());

        return response()->json([
            'message' => 'Appeal submitted successfully.',
            'appeal' => new EmployeeRelationAppealResource($appeal),
        ], 201);
    }

    public function resolve(AppealResolveRequest $request, EmployeeRelationCase $case, EmployeeRelationAppeal $appeal): JsonResponse
    {
        $this->authorizeCase($request, $case);
        abort_unless($this->authService->canManageAppeal($request->user(), $case), 403);

        $resolved = $this->appealService->resolveAppeal($appeal, $request->user(), $request->validated());

        return response()->json([
            'message' => 'Appeal resolved successfully.',
            'appeal' => new EmployeeRelationAppealResource($resolved),
        ]);
    }
}
