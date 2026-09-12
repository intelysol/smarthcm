<?php

namespace App\Domains\Career\Http\Controllers;

use App\Domains\Career\Models\CareerPlan;
use App\Domains\Career\Models\CareerPlanAction;
use App\Domains\Career\Models\CareerSkillGap;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Career\Models\SuccessionPosition;
use App\Domains\Career\Models\TalentPoolMember;
use App\Domains\Career\Services\CareerTalentAnalyticsService;
use App\Domains\Career\Services\SuccessionRiskEngine;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTalentReportController extends Controller
{
    public function __construct(
        protected CareerTalentAnalyticsService $analyticsService,
        protected SuccessionRiskEngine $riskEngine
    ) {}

    public function skillInventory(Request $request): JsonResponse
    {
        $skills = EmployeeSkill::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['employee.department', 'skill.category', 'verifier'])
            ->paginate(30);

        return response()->json($skills);
    }

    public function skillGaps(Request $request): JsonResponse
    {
        $gaps = CareerSkillGap::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('status', 'open')
            ->with(['employee.department', 'skill', 'targetJob'])
            ->paginate(30);

        return response()->json($gaps);
    }

    public function careerReadiness(Request $request): JsonResponse
    {
        $plans = CareerPlan::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['employee.department', 'targetJob'])
            ->paginate(30);

        return response()->json($plans);
    }

    public function talentPools(Request $request): JsonResponse
    {
        $members = TalentPoolMember::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['pool', 'employee.department', 'employee.designation'])
            ->paginate(30);

        return response()->json($members);
    }

    public function successionCoverage(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $coverage = $this->riskEngine->calculateCoverage($tenantId);

        $positions = SuccessionPosition::query()
            ->where('tenant_id', $tenantId)
            ->with(['position', 'job', 'incumbent', 'candidates.employee'])
            ->get();

        return response()->json([
            'coverage' => $coverage,
            'positions' => $positions,
        ]);
    }

    public function development(Request $request): JsonResponse
    {
        $actions = CareerPlanAction::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['plan.employee', 'plan.targetJob'])
            ->paginate(30);

        return response()->json($actions);
    }

    public function analyticsSnapshot(Request $request): JsonResponse
    {
        $snapshot = $this->analyticsService->generateSnapshot($request->user()->tenant_id);
        return response()->json(['data' => $snapshot]);
    }
}
