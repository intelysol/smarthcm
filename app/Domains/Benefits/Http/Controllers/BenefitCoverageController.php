<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Services\BenefitCoverageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitCoverageController extends Controller
{
    public function __construct(
        protected BenefitCoverageService $coverageService
    ) {}

    public function index(BenefitPlan $plan): JsonResponse
    {
        $coverages = $this->coverageService->getCoveragesForPlan($plan);

        return response()->json([
            'success' => true,
            'plan_id' => $plan->id,
            'data' => $coverages,
        ]);
    }

    public function store(BenefitPlan $plan, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'coverage_multiplier' => 'nullable|numeric',
            'employee_cost_factor' => 'nullable|numeric',
            'employer_cost_factor' => 'nullable|numeric',
            'fixed_employee_cost' => 'nullable|numeric',
            'fixed_employer_cost' => 'nullable|numeric',
            'max_dependents' => 'nullable|integer',
        ]);

        $coverage = $this->coverageService->createCoverage($plan, $validated, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Benefit coverage tier created successfully.',
            'data' => $coverage,
        ], 201);
    }

    public function estimate(BenefitPlan $plan, Request $request): JsonResponse
    {
        $coverage = null;
        if ($request->has('coverage_id')) {
            $coverage = $plan->coverages()->find($request->query('coverage_id'));
        }

        $costs = $this->coverageService->calculateEstimatedCosts($plan, $coverage);

        return response()->json([
            'success' => true,
            'data' => $costs,
        ]);
    }
}
