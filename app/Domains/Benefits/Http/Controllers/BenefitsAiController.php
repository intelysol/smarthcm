<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Services\BenefitsAiAdvisoryService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitsAiController extends Controller
{
    public function __construct(
        protected BenefitsAiAdvisoryService $aiService
    ) {}

    public function explainPlans(Employee $employee): JsonResponse
    {
        $response = $this->aiService->explainAvailablePlans($employee);

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function comparePlans(Employee $employee, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_ids' => 'required|array|min:1',
            'plan_ids.*' => 'uuid|exists:benefit_plans,id',
        ]);

        $response = $this->aiService->comparePlans($validated['plan_ids'], $employee);

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function explainEligibility(Employee $employee, BenefitPlan $plan): JsonResponse
    {
        $response = $this->aiService->explainEligibility($employee, $plan);

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function chat(Employee $employee, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:1000',
        ]);

        $response = $this->aiService->processAdvisoryPrompt($validated['prompt'], $employee);

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
}
