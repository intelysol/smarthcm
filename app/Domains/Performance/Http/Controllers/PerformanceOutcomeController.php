<?php

declare(strict_types=1);

namespace App\Domains\Performance\Http\Controllers;

use App\Domains\Performance\Models\PerformanceFinalOutcome;
use App\Domains\Performance\Models\PerformanceReviewAppeal;
use App\Domains\Performance\Services\PerformanceFinalOutcomeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceOutcomeController extends Controller
{
    public function show(Request $request, string $cycleId, string $employeeId): JsonResponse
    {
        $outcome = PerformanceFinalOutcome::where('cycle_id', $cycleId)
            ->where('employee_id', $employeeId)
            ->firstOrFail();

        return response()->json(['data' => $outcome]);
    }

    public function publish(Request $request, PerformanceFinalOutcomeService $service): JsonResponse
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'string'],
            'cycle_id' => ['required', 'string'],
            'employee_id' => ['required', 'string'],
            'final_rating' => ['required', 'numeric'],
            'final_score' => ['nullable', 'numeric'],
            'summary' => ['nullable', 'string'],
            'strengths' => ['nullable', 'string'],
            'development_areas' => ['nullable', 'string'],
        ]);

        $outcome = $service->publishOutcome($data, $request->user());
        return response()->json(['data' => $outcome], 201);
    }

    public function acknowledge(Request $request, PerformanceFinalOutcome $outcome, PerformanceFinalOutcomeService $service): JsonResponse
    {
        $data = $request->validate([
            'comment' => ['nullable', 'string'],
        ]);

        $acknowledged = $service->acknowledgeReview($outcome, $data['comment'] ?? null);
        return response()->json(['data' => $acknowledged]);
    }

    public function submitAppeal(Request $request, PerformanceFinalOutcomeService $service): JsonResponse
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'string'],
            'review_id' => ['required', 'string'],
            'employee_id' => ['required', 'string'],
            'reason' => ['required', 'string'],
        ]);

        $appeal = $service->submitAppeal($data['tenant_id'], $data['review_id'], $data['employee_id'], $data['reason']);
        return response()->json(['data' => $appeal], 201);
    }

    public function resolveAppeal(Request $request, PerformanceReviewAppeal $appeal, PerformanceFinalOutcomeService $service): JsonResponse
    {
        $data = $request->validate([
            'resolution' => ['required', 'string'],
        ]);

        $resolved = $service->resolveAppeal($appeal, $data['resolution'], $request->user());
        return response()->json(['data' => $resolved]);
    }
}