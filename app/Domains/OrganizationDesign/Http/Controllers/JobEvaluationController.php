<?php

namespace App\Domains\OrganizationDesign\Http\Controllers;

use App\Domains\OrganizationDesign\Models\JobProfile;
use App\Domains\OrganizationDesign\Services\JobEvaluationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobEvaluationController extends Controller
{
    public function __construct(
        protected JobEvaluationService $evaluationService
    ) {}

    public function evaluate(Request $request, string $profileId): JsonResponse
    {
        $profile = JobProfile::findOrFail($profileId);
        $validated = $request->validate([
            'evaluation_model' => 'nullable|string',
            'knowledge_score' => 'required|integer|min:0|max:1000',
            'problem_solving_score' => 'required|integer|min:0|max:1000',
            'accountability_score' => 'required|integer|min:0|max:1000',
            'impact_score' => 'required|integer|min:0|max:1000',
            'leadership_score' => 'required|integer|min:0|max:1000',
            'notes' => 'nullable|string',
        ]);

        $evaluation = $this->evaluationService->evaluateJob($profile, $validated, $request->user());

        return response()->json($evaluation, 201);
    }
}
