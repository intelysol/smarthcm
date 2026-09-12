<?php

namespace App\Domains\WorkforcePlanning\Http\Controllers;

use App\Domains\WorkforcePlanning\Models\HcmWorkforceHiringPlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Services\HiringPlanService;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanningSecurityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HiringPlanController extends Controller
{
    public function __construct(
        protected HiringPlanService $hiringService,
        protected WorkforcePlanningSecurityService $securityService
    ) {
    }

    public function index(Request $request, string $planId): JsonResponse
    {
        $plan = HcmWorkforcePlan::findOrFail($planId);
        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $plan);
        }

        $hiringPlans = $plan->hiringPlans()->with(['department', 'jobGrade', 'positionPlan', 'hiringManager'])->paginate(25);
        return response()->json($hiringPlans);
    }

    public function store(Request $request, string $planId): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'planned_start_date' => 'required|date',
            'department_id' => 'nullable|uuid',
            'job_grade_id' => 'nullable|uuid',
            'position_plan_id' => 'nullable|uuid',
            'priority' => 'nullable|string|in:low,medium,high,critical',
            'reason' => 'nullable|string|in:growth,replacement,new_capability,expansion,backfill',
            'replacement_type' => 'nullable|string|in:immediate,delayed,internal,external,none',
            'replaces_employee_id' => 'nullable|uuid',
            'hiring_manager_id' => 'nullable|integer',
        ]);

        $plan = HcmWorkforcePlan::findOrFail($planId);
        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $plan);
        }

        $hiringPlan = $this->hiringService->createHiringRequirement($plan, $request->all());
        return response()->json($hiringPlan, 201);
    }

    public function handoff(Request $request, string $id): JsonResponse
    {
        $hiringPlan = HcmWorkforceHiringPlan::findOrFail($id);
        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $hiringPlan->plan);
        }

        $payload = $this->hiringService->generateRecruitmentHandoffPayload($hiringPlan);
        return response()->json([
            'message' => 'Recruitment requisition payload generated successfully.',
            'payload' => $payload,
        ]);
    }
}
