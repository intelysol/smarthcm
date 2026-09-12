<?php

namespace App\Domains\WorkforceOptimization\Http\Controllers;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;
use App\Domains\WorkforceOptimization\Services\AdvisoryWorkforceOptimizationAiService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvisoryOptimizationAiController extends Controller
{
    public function __construct(
        protected AdvisoryWorkforceOptimizationAiService $aiService
    ) {}

    public function explain(string $recommendationId): JsonResponse
    {
        $rec = HcmWorkforceOptimizationRecommendation::findOrFail($recommendationId);
        $advisory = $this->aiService->generateAdvisoryExplanation($rec);

        return response()->json([
            'success' => true,
            'recommendation_id' => $rec->id,
            'data' => $advisory,
        ]);
    }
}
