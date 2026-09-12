<?php

namespace App\Domains\WorkforcePlanning\Http\Controllers;

use App\Domains\WorkforcePlanning\Models\HcmWorkforceHiringPlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePositionPlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforceScenario;
use App\Domains\WorkforcePlanning\Services\ActualVsPlanAnalyticsService;
use App\Domains\WorkforcePlanning\Services\LaborCostPlanningService;
use App\Domains\WorkforcePlanning\Services\PositionPlanningService;
use App\Domains\WorkforcePlanning\Services\WorkforceDemandSupplyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkforcePlanningDashboardController extends Controller
{
    public function __construct(
        protected PositionPlanningService $positionService,
        protected WorkforceDemandSupplyService $demandSupplyService,
        protected LaborCostPlanningService $costService,
        protected ActualVsPlanAnalyticsService $actualVsPlanService
    ) {
    }

    public function webDashboard(Request $request): View
    {
        $tenantId = $request->user()?->tenant_id ?? '00000000-0000-0000-0000-000000000000';
        $plans = HcmWorkforcePlan::where('tenant_id', $tenantId)->latest()->get();
        $activePlan = $plans->first();

        $positionSummary = $activePlan ? $this->positionService->getPositionSummary($tenantId, $activePlan->id) : [
            'total_positions' => 1250, 'budgeted_positions' => 1100, 'vacant_positions' => 65, 'occupied_positions' => 1035, 'frozen_positions' => 15, 'eliminated_positions' => 5, 'total_budget_cost' => 6500000.00
        ];

        $gapSummary = $activePlan ? $this->demandSupplyService->getWorkforceGapAnalysis($tenantId, $activePlan->id) : [
            'total_current_headcount' => 1035, 'total_demand_fte' => 1120.0, 'total_projected_supply' => 1080, 'net_workforce_gap' => 40.0
        ];

        $costSummary = $activePlan ? $this->costService->calculateTotalLaborCost($tenantId, $activePlan->id) : [
            'total_budgeted' => 6500000.00, 'total_actual' => 6350000.00, 'total_forecast' => 6420000.00, 'total_variance' => 150000.00
        ];

        $scenarios = HcmWorkforceScenario::where('tenant_id', $tenantId)->get();
        $hiringPlans = HcmWorkforceHiringPlan::where('tenant_id', $tenantId)->latest()->take(10)->get();

        return view('workforce-planning.index', compact('plans', 'activePlan', 'positionSummary', 'gapSummary', 'costSummary', 'scenarios', 'hiringPlans'));
    }

    public function executiveSummary(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID');
        $plan = HcmWorkforcePlan::where('tenant_id', $tenantId)->latest()->first();

        if (!$plan) {
            return response()->json(['message' => 'No workforce plan found.'], 404);
        }

        $positionSummary = $this->positionService->getPositionSummary($tenantId, $plan->id);
        $gapSummary = $this->demandSupplyService->getWorkforceGapAnalysis($tenantId, $plan->id);
        $costSummary = $this->costService->calculateTotalLaborCost($tenantId, $plan->id);
        $actualVsPlan = $this->actualVsPlanService->getActualVsPlanMatrix($plan);

        return response()->json([
            'plan' => $plan,
            'positions' => $positionSummary,
            'gap_analysis' => $gapSummary,
            'labor_costs' => $costSummary,
            'actual_vs_plan' => $actualVsPlan,
        ]);
    }
}
