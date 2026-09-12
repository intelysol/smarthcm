<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Http\Controllers;

use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Models\CompensationRecommendation;
use App\Domains\Compensation\Services\MeritPlanningService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompensationRecommendationController extends Controller
{
    public function __construct(
        protected MeritPlanningService $meritService
    ) {}

    public function index(CompensationCycle $cycle): JsonResponse
    {
        $recommendations = $cycle->recommendations()
            ->with(['employee', 'jobGrade'])
            ->get();

        return response()->json(['data' => $recommendations]);
    }

    public function propose(Request $request, CompensationCycle $cycle, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'increase_percentage' => 'required|numeric|min:0|max:100',
            'performance_rating' => 'nullable|string|max:50',
            'justification' => 'nullable|string',
            'promotion_amount' => 'nullable|numeric|min:0',
            'market_adjustment_amount' => 'nullable|numeric|min:0',
            'lump_sum_amount' => 'nullable|numeric|min:0',
            'promotion_grade_id' => 'nullable|uuid',
        ]);

        $recommendation = $this->meritService->generateOrUpdateRecommendation(
            $request->user(),
            $cycle,
            $employee,
            (float) $validated['increase_percentage'],
            $validated['performance_rating'] ?? null,
            $validated['justification'] ?? null,
            (float) ($validated['promotion_amount'] ?? 0.0),
            (float) ($validated['market_adjustment_amount'] ?? 0.0),
            (float) ($validated['lump_sum_amount'] ?? 0.0),
            $validated['promotion_grade_id'] ?? null
        );

        return response()->json(['data' => $recommendation], 200);
    }

    public function approve(Request $request, CompensationRecommendation $recommendation): JsonResponse
    {
        $approved = $this->meritService->approveRecommendation($request->user(), $recommendation);

        return response()->json(['data' => $approved]);
    }
}
