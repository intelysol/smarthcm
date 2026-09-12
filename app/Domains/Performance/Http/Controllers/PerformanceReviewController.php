<?php

declare(strict_types=1);

namespace App\Domains\Performance\Http\Controllers;

use App\Domains\Performance\Models\PerformanceReview;
use App\Domains\Performance\Services\PerformanceReviewService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceReviewController extends Controller
{
    public function selfAssessment(Request $request, PerformanceReview $review, PerformanceReviewService $service): JsonResponse
    {
        $data = $request->validate([
            'goal_assessment' => ['nullable', 'array'],
            'competency_assessment' => ['nullable', 'array'],
            'achievements' => ['nullable', 'string'],
            'challenges' => ['nullable', 'string'],
            'development' => ['nullable', 'string'],
            'overall_self_rating' => ['nullable', 'numeric'],
            'comments' => ['nullable', 'string'],
        ]);

        $assessment = $service->submitSelfAssessment($review, $data, $request->user());
        return response()->json(['data' => $assessment]);
    }

    public function managerAssessment(Request $request, PerformanceReview $review, PerformanceReviewService $service): JsonResponse
    {
        $data = $request->validate([
            'overall_rating' => ['required', 'numeric'],
            'calculated_rating' => ['nullable', 'numeric'],
            'summary' => ['nullable', 'string'],
        ]);

        $updatedReview = $service->submitManagerAssessment($review, $data, $request->user());
        return response()->json(['data' => $updatedReview]);
    }
}
