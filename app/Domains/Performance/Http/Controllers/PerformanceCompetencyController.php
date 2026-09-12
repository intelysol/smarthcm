<?php

declare(strict_types=1);

namespace App\Domains\Performance\Http\Controllers;

use App\Domains\Performance\Models\Competency;
use App\Domains\Performance\Models\CompetencyFramework;
use App\Domains\Performance\Models\PerformanceReview;
use App\Domains\Performance\Services\PerformanceCompetencyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceCompetencyController extends Controller
{
    public function frameworks(Request $request): JsonResponse
    {
        $frameworks = CompetencyFramework::with('categories.competencies.levels')->get();
        return response()->json(['data' => $frameworks]);
    }

    public function recordAssessment(Request $request, PerformanceReview $review, PerformanceCompetencyService $service): JsonResponse
    {
        $data = $request->validate([
            'competency_id' => ['required', 'string'],
            'rating' => ['required', 'numeric'],
            'comment' => ['nullable', 'string'],
            'evidence' => ['nullable', 'string'],
        ]);

        $assessment = $service->recordAssessment(
            $review,
            $data['competency_id'],
            (float) $data['rating'],
            $data['comment'] ?? null,
            $data['evidence'] ?? null
        );

        return response()->json(['data' => $assessment], 201);
    }

    public function evaluateGaps(Request $request, PerformanceReview $review, PerformanceCompetencyService $service): JsonResponse
    {
        $data = $request->validate([
            'expected_levels' => ['required', 'array'],
        ]);

        $gaps = $service->evaluateGaps($review, $data['expected_levels']);
        return response()->json(['data' => $gaps]);
    }
}