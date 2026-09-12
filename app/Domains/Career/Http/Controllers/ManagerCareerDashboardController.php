<?php

namespace App\Domains\Career\Http\Controllers;

use App\Domains\Career\Models\CareerPlan;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Career\Resources\CareerPlanResource;
use App\Domains\Career\Resources\EmployeeSkillResource;
use App\Domains\Career\Services\CareerPlanService;
use App\Domains\Career\Services\CareerSkillService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagerCareerDashboardController extends Controller
{
    public function __construct(
        protected CareerSkillService $skillService,
        protected CareerPlanService $planService
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        $manager = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $subordinateIds = Employee::query()
            ->where('tenant_id', $manager->tenant_id)
            ->where('reporting_manager_id', $manager->id)
            ->pluck('id');

        $pendingVerifications = EmployeeSkill::query()
            ->where('tenant_id', $manager->tenant_id)
            ->whereIn('employee_id', $subordinateIds)
            ->where('verification_status', 'self_declared')
            ->with(['employee', 'skill', 'evidence'])
            ->get();

        $teamPlans = CareerPlan::query()
            ->where('tenant_id', $manager->tenant_id)
            ->whereIn('employee_id', $subordinateIds)
            ->where('visibility', '!=', 'private')
            ->with(['employee', 'targetJob', 'actions'])
            ->get();

        return response()->json([
            'team_size' => $subordinateIds->count(),
            'pending_verifications_count' => $pendingVerifications->count(),
            'pending_verifications' => EmployeeSkillResource::collection($pendingVerifications),
            'team_plans' => CareerPlanResource::collection($teamPlans),
        ]);
    }

    public function teamSkills(Request $request): JsonResponse
    {
        $manager = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $teamMembers = Employee::query()
            ->where('tenant_id', $manager->tenant_id)
            ->where('reporting_manager_id', $manager->id)
            ->with(['skills.skill', 'skillGaps.skill'])
            ->paginate(15);

        return response()->json($teamMembers);
    }

    public function verifySkill(Request $request, string $employeeSkillId): JsonResponse
    {
        $manager = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $subordinateIds = Employee::query()
            ->where('tenant_id', $manager->tenant_id)
            ->where('reporting_manager_id', $manager->id)
            ->pluck('id');

        $employeeSkill = EmployeeSkill::query()
            ->where('tenant_id', $manager->tenant_id)
            ->whereIn('employee_id', $subordinateIds)
            ->findOrFail($employeeSkillId);

        $verified = $this->skillService->verifySkill($employeeSkill, $manager, 'manager_verified');

        return response()->json(['data' => new EmployeeSkillResource($verified->load(['skill', 'evidence']))], 200);
    }

    public function approvePlan(Request $request, string $planId): JsonResponse
    {
        $manager = Employee::query()->where('user_id', $request->user()->id)->firstOrFail();

        $subordinateIds = Employee::query()
            ->where('tenant_id', $manager->tenant_id)
            ->where('reporting_manager_id', $manager->id)
            ->pluck('id');

        $plan = CareerPlan::query()
            ->where('tenant_id', $manager->tenant_id)
            ->whereIn('employee_id', $subordinateIds)
            ->findOrFail($planId);

        $approved = $this->planService->approvePlan($plan, $manager);

        return response()->json(['data' => new CareerPlanResource($approved->load(['targetJob', 'actions']))], 200);
    }
}
