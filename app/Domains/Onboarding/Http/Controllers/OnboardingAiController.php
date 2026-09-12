<?php

namespace App\Domains\Onboarding\Http\Controllers;

use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Services\OnboardingAiService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingAiController extends Controller
{
    public function __construct(protected OnboardingAiService $aiService)
    {
    }

    public function welcome(Request $request, string $caseId): JsonResponse
    {
        $case = HcmOnboardingCase::with('employee.department')->findOrFail($caseId);
        $welcome = $this->aiService->generateWelcomeMessage($case->employee, $case);

        return response()->json($welcome);
    }

    public function readiness(string $caseId): JsonResponse
    {
        $case = HcmOnboardingCase::with(['tasks', 'documentRequirements'])->findOrFail($caseId);
        $summary = $this->aiService->summarizeCaseReadiness($case);

        return response()->json($summary);
    }

    public function query(Request $request): JsonResponse
    {
        $request->validate(['query' => 'required|string|max:1000']);
        $answer = $this->aiService->answerOnboardingInquiry($request->user()->tenant_id, $request->input('query'));

        return response()->json($answer);
    }
}
