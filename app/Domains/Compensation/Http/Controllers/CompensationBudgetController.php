<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Http\Controllers;

use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Services\CompensationBudgetService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompensationBudgetController extends Controller
{
    public function __construct(
        protected CompensationBudgetService $budgetService
    ) {}

    public function index(CompensationCycle $cycle): JsonResponse
    {
        $budgets = $this->budgetService->getCycleBudgets($cycle);
        $rollup = $this->budgetService->calculateRollup($cycle);

        return response()->json([
            'data' => $budgets,
            'rollup' => $rollup,
        ]);
    }

    public function allocate(Request $request, CompensationCycle $cycle): JsonResponse
    {
        $validated = $request->validate([
            'scope_type' => 'required|string|max:30',
            'scope_id' => 'required|string|max:80',
            'allocated' => 'required|numeric|min:0',
            'budget_type' => 'nullable|string|max:30',
            'holdback_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
        ]);

        $budget = $this->budgetService->allocate(
            $request->user(),
            $cycle,
            $validated['scope_type'],
            $validated['scope_id'],
            (float) $validated['allocated'],
            $validated['budget_type'] ?? 'merit',
            (float) ($validated['holdback_amount'] ?? 0.0),
            $validated['currency'] ?? null
        );

        return response()->json(['data' => $budget], 201);
    }
}
