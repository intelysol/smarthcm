<?php

namespace App\Domains\Learning\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Services\LearningAiRecommendationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearningAiController extends Controller
{
    public function __construct(protected LearningAiRecommendationService $aiService)
    {
    }

    public function recommendations(Request $request): JsonResponse
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();
        $skillGaps = $request->input('skill_gaps', []);

        $result = $this->aiService->getRecommendationsForEmployee($employee, $skillGaps);
        return response()->json($result);
    }

    public function query(Request $request): JsonResponse
    {
        $request->validate(['query' => 'required|string|max:1000']);
        $tenantId = $request->user()->tenant_id ?? '00000000-0000-0000-0000-000000000000';

        $result = $this->aiService->answerLearningQuery($tenantId, $request->input('query'));
        return response()->json($result);
    }
}
