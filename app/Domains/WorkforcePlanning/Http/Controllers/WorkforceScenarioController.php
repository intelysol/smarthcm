<?php

namespace App\Domains\WorkforcePlanning\Http\Controllers;

use App\Domains\WorkforcePlanning\Models\HcmWorkforcePlan;
use App\Domains\WorkforcePlanning\Models\HcmWorkforceScenario;
use App\Domains\WorkforcePlanning\Requests\CreateScenarioRequest;
use App\Domains\WorkforcePlanning\Services\WorkforcePlanningSecurityService;
use App\Domains\WorkforcePlanning\Services\WorkforceScenarioService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkforceScenarioController extends Controller
{
    public function __construct(
        protected WorkforceScenarioService $scenarioService,
        protected WorkforcePlanningSecurityService $securityService
    ) {
    }

    public function index(Request $request, string $planId): JsonResponse
    {
        $plan = HcmWorkforcePlan::findOrFail($planId);
        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $plan);
        }

        $scenarios = $plan->scenarios()->with(['headcounts', 'costs'])->get();
        return response()->json($scenarios);
    }

    public function store(CreateScenarioRequest $request, string $planId): JsonResponse
    {
        $plan = HcmWorkforcePlan::findOrFail($planId);
        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $plan);
        }

        $scenario = $this->scenarioService->createScenario($plan, $request->validated(), $request->user()?->id);
        return response()->json($scenario, 201);
    }

    public function simulate(Request $request, string $id): JsonResponse
    {
        $scenario = HcmWorkforceScenario::findOrFail($id);
        if ($request->user()) {
            $this->securityService->authorizePlanAccess($request->user(), $scenario->basePlan);
        }

        $result = $this->scenarioService->simulateScenario($scenario);
        return response()->json($result);
    }

    public function compare(Request $request): JsonResponse
    {
        $request->validate(['scenario_ids' => 'required|array|min:1']);
        $matrix = $this->scenarioService->compareScenarios($request->input('scenario_ids'));
        return response()->json($matrix);
    }
}
