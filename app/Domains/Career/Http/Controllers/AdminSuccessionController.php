<?php

namespace App\Domains\Career\Http\Controllers;

use App\Domains\Career\Models\SuccessionCandidate;
use App\Domains\Career\Models\SuccessionPlan;
use App\Domains\Career\Models\SuccessionPosition;
use App\Domains\Career\Models\SuccessionScenario;
use App\Domains\Career\Requests\SuccessionCandidateRequest;
use App\Domains\Career\Requests\SuccessionPlanRequest;
use App\Domains\Career\Resources\SuccessionPlanResource;
use App\Domains\Career\Services\SuccessionPlanService;
use App\Domains\Career\Services\SuccessionRiskEngine;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Job;
use App\Domains\Organization\Models\Position;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSuccessionController extends Controller
{
    public function __construct(
        protected SuccessionPlanService $planService,
        protected SuccessionRiskEngine $riskEngine
    ) {}

    public function plans(Request $request): JsonResponse
    {
        $plans = SuccessionPlan::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->withCount('positions')
            ->get();

        return response()->json(SuccessionPlanResource::collection($plans));
    }

    public function storePlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'scope_type' => ['nullable', 'string'],
            'scope_id' => ['nullable', 'string'],
        ]);

        $plan = $this->planService->createPlan(
            $request->user()->tenant_id,
            $validated['name'],
            $validated['description'] ?? null,
            $validated['scope_type'] ?? 'organization',
            $validated['scope_id'] ?? null
        );

        return response()->json(['data' => new SuccessionPlanResource($plan)], 201);
    }

    public function positions(Request $request, string $planId): JsonResponse
    {
        $plan = SuccessionPlan::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($planId);

        $positions = $plan->positions()
            ->with(['position', 'job', 'department', 'incumbent', 'candidates.employee', 'emergencySuccessor'])
            ->get();

        return response()->json(['data' => $positions]);
    }

    public function storePosition(Request $request, string $planId): JsonResponse
    {
        $plan = SuccessionPlan::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($planId);

        $validated = $request->validate([
            'position_id' => ['required', 'string', 'exists:positions,id'],
            'job_id' => ['required', 'string', 'exists:organization_job_definitions,id'],
            'current_incumbent_id' => ['nullable', 'string', 'exists:employees,id'],
            'criticality' => ['nullable', 'string', 'in:critical,business_critical,leadership_critical,technical_critical'],
            'vacancy_risk' => ['nullable', 'string', 'in:low,medium,high,critical'],
        ]);

        $position = Position::query()->where('tenant_id', $plan->tenant_id)->findOrFail($validated['position_id']);
        $job = Job::query()->where('tenant_id', $plan->tenant_id)->findOrFail($validated['job_id']);
        $incumbent = ! empty($validated['current_incumbent_id'])
            ? Employee::query()->where('tenant_id', $plan->tenant_id)->find($validated['current_incumbent_id'])
            : null;

        $succPosition = $this->planService->addPositionToPlan(
            $plan,
            $position,
            $job,
            $incumbent,
            $validated['criticality'] ?? 'critical',
            $validated['vacancy_risk'] ?? 'medium'
        );

        return response()->json(['data' => $succPosition->load(['position', 'job', 'incumbent'])], 201);
    }

    public function candidates(Request $request, string $positionId): JsonResponse
    {
        $position = SuccessionPosition::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($positionId);

        $candidates = $position->candidates()->with(['employee.department', 'employee.designation', 'developmentActions'])->get();
        return response()->json(['data' => $candidates]);
    }

    public function storeCandidate(SuccessionCandidateRequest $request, string $positionId): JsonResponse
    {
        $position = SuccessionPosition::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($positionId);

        $employee = Employee::query()
            ->where('tenant_id', $position->tenant_id)
            ->findOrFail($request->validated('employee_id'));

        $candidate = $this->planService->addCandidate(
            $position,
            $employee,
            (int) $request->validated('priority', 1),
            $request->validated('readiness_timeframe'),
            (float) $request->validated('readiness_score', 70.0),
            $request->validated('potential_rating') ? (float) $request->validated('potential_rating') : null,
            $request->validated('performance_rating') ? (float) $request->validated('performance_rating') : null,
            (bool) $request->validated('is_emergency_choice', false)
        );

        return response()->json(['data' => $candidate->load('employee')], 201);
    }

    public function scenarios(Request $request, string $planId): JsonResponse
    {
        $plan = SuccessionPlan::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($planId);

        $scenarios = $plan->scenarios()->with(['scenarioCandidates.proposedSuccessor', 'scenarioCandidates.position'])->get();
        return response()->json(['data' => $scenarios]);
    }

    public function storeScenario(Request $request, string $planId): JsonResponse
    {
        $plan = SuccessionPlan::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($planId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'trigger_event' => ['required', 'string', 'in:incumbent_leaves,incumbent_promoted,incumbent_retires,position_expands,new_position'],
            'description' => ['nullable', 'string'],
        ]);

        $scenario = $this->planService->createScenario(
            $plan,
            $validated['name'],
            $validated['trigger_event'],
            $validated['description'] ?? null
        );

        return response()->json(['data' => $scenario], 201);
    }

    public function storeScenarioCandidate(Request $request, string $scenarioId): JsonResponse
    {
        $scenario = SuccessionScenario::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($scenarioId);

        $validated = $request->validate([
            'succession_position_id' => ['required', 'string', 'exists:succession_positions,id'],
            'proposed_successor_id' => ['required', 'string', 'exists:employees,id'],
            'role_assignment' => ['nullable', 'string', 'in:permanent,interim,acting'],
            'impact_analysis' => ['nullable', 'string'],
        ]);

        $position = SuccessionPosition::query()->where('tenant_id', $scenario->tenant_id)->findOrFail($validated['succession_position_id']);
        $proposedSuccessor = Employee::query()->where('tenant_id', $scenario->tenant_id)->findOrFail($validated['proposed_successor_id']);

        $candidate = $this->planService->addScenarioCandidate(
            $scenario,
            $position,
            $proposedSuccessor,
            $validated['role_assignment'] ?? 'permanent',
            $validated['impact_analysis'] ?? null
        );

        return response()->json(['data' => $candidate->load(['position', 'proposedSuccessor'])], 201);
    }

    public function riskSummary(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $coverage = $this->riskEngine->calculateCoverage($tenantId);

        $highRiskPositions = SuccessionPosition::query()
            ->where('tenant_id', $tenantId)
            ->where('risk_score', '>=', 60.0)
            ->with(['position', 'job', 'incumbent'])
            ->get();

        return response()->json([
            'coverage' => $coverage,
            'high_risk_positions' => $highRiskPositions,
        ]);
    }
}
