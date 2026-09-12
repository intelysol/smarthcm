<?php

namespace App\Domains\Career\Http\Controllers;

use App\Domains\Career\Models\CareerDevelopmentRecommendation;
use App\Domains\Career\Models\CareerMentoringRelationship;
use App\Domains\Career\Models\CareerPath;
use App\Domains\Career\Models\CareerPathStep;
use App\Domains\Career\Models\CareerPlan;
use App\Domains\Career\Models\CareerPlanAction;
use App\Domains\Career\Models\EmployeeCareerAspiration;
use App\Domains\Career\Requests\CareerPlanActionRequest;
use App\Domains\Career\Requests\CareerPlanCreateRequest;
use App\Domains\Career\Resources\CareerPlanResource;
use App\Domains\Career\Services\CareerEligibilityService;
use App\Domains\Career\Services\CareerJobMatchingService;
use App\Domains\Career\Services\CareerPlanService;
use App\Domains\Career\Services\CareerReadinessEngine;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Job;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeCareerDashboardController extends Controller
{
    public function __construct(
        protected CareerPlanService $planService,
        protected CareerReadinessEngine $readinessEngine,
        protected CareerEligibilityService $eligibilityService,
        protected CareerJobMatchingService $matchingService
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $aspiration = EmployeeCareerAspiration::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->where('status', 'active')
            ->with('targetJob')
            ->first();

        $activePlan = CareerPlan::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['draft', 'under_review', 'approved', 'in_progress'])
            ->with(['targetJob', 'actions'])
            ->latest()
            ->first();

        $recommendations = CareerDevelopmentRecommendation::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['suggested', 'accepted', 'in_progress'])
            ->get();

        $mentoring = CareerMentoringRelationship::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where(function ($q) use ($employee) {
                $q->where('mentee_employee_id', $employee->id)
                  ->orWhere('mentor_employee_id', $employee->id);
            })
            ->where('status', 'active')
            ->with(['mentor', 'mentee', 'goals'])
            ->first();

        return response()->json([
            'aspiration' => $aspiration,
            'active_plan' => $activePlan ? new CareerPlanResource($activePlan) : null,
            'recommendations' => $recommendations,
            'mentoring' => $mentoring,
            'internal_opportunities' => $this->matchingService->findMatchingJobsForEmployee($employee, 3),
        ]);
    }

    public function careerPath(Request $request): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $path = CareerPath::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('status', 'active')
            ->with(['steps.job'])
            ->first();

        $stepEvaluations = [];
        if ($path) {
            foreach ($path->steps as $step) {
                $stepEvaluations[$step->id] = $this->eligibilityService->evaluateStepEligibility($employee, $step);
            }
        }

        return response()->json([
            'path' => $path,
            'evaluations' => $stepEvaluations,
        ]);
    }

    public function storeAspiration(Request $request): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $validated = $request->validate([
            'target_job_id' => ['nullable', 'string', 'exists:organization_job_definitions,id'],
            'target_career_level' => ['nullable', 'string'],
            'preferred_timeline_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'preferred_location' => ['nullable', 'string'],
            'interest_areas' => ['nullable', 'array'],
            'preferences' => ['nullable', 'array'],
            'visibility' => ['nullable', 'string', 'in:private,manager_shared,hr_shared,talent_pool'],
            'notes' => ['nullable', 'string'],
        ]);

        $aspiration = EmployeeCareerAspiration::query()->updateOrCreate(
            [
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
            ],
            array_merge($validated, ['status' => 'active'])
        );

        return response()->json(['data' => $aspiration], 200);
    }

    public function plans(Request $request): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $plans = CareerPlan::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->with(['targetJob', 'actions'])
            ->latest()
            ->paginate(15);

        return response()->json(CareerPlanResource::collection($plans));
    }

    public function storePlan(CareerPlanCreateRequest $request): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();
        $targetJob = $request->validated('target_job_id')
            ? Job::query()->where('tenant_id', $employee->tenant_id)->findOrFail($request->validated('target_job_id'))
            : null;

        $plan = $this->planService->createPlan(
            $employee,
            $targetJob,
            $request->validated('target_date'),
            $request->validated('visibility', 'private')
        );

        return response()->json(['data' => new CareerPlanResource($plan->load(['targetJob', 'actions']))], 201);
    }

    public function storePlanAction(CareerPlanActionRequest $request, string $planId): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();
        $plan = CareerPlan::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('employee_id', $employee->id)
            ->findOrFail($planId);

        $action = $this->planService->addAction(
            $plan,
            $request->validated('action_type'),
            $request->validated('title'),
            $request->validated('description'),
            $request->validated('start_date'),
            $request->validated('due_date')
        );

        return response()->json(['data' => $action], 201);
    }

    public function updateActionProgress(Request $request, string $actionId): JsonResponse
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();
        $action = CareerPlanAction::query()
            ->where('tenant_id', $employee->tenant_id)
            ->whereHas('plan', fn ($q) => $q->where('employee_id', $employee->id))
            ->findOrFail($actionId);

        $validated = $request->validate([
            'completion_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', 'string', 'in:planned,in_progress,completed,cancelled'],
        ]);

        $updated = $this->planService->updateActionProgress(
            $action,
            (float) $validated['completion_percentage'],
            $validated['status'] ?? 'in_progress'
        );

        return response()->json(['data' => $updated], 200);
    }
}
