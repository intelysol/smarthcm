<?php

namespace App\Domains\Onboarding\Http\Controllers;

use App\Domains\Onboarding\Models\HcmOnboardingProbation;
use App\Domains\Onboarding\Services\OnboardingProbationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingProbationController extends Controller
{
    public function __construct(protected OnboardingProbationService $probationService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $probations = HcmOnboardingProbation::where('tenant_id', $tenantId)
            ->with(['employee.department', 'reviews'])
            ->latest()
            ->paginate(15);

        return response()->json($probations);
    }

    public function extend(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'new_end_date' => 'required|date',
            'reason' => 'required|string|max:255',
        ]);

        $probation = HcmOnboardingProbation::findOrFail($id);
        $extended = $this->probationService->extendProbation(
            $probation,
            $validated['new_end_date'],
            $validated['reason']
        );

        return response()->json($extended);
    }

    public function review(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'performance_rating' => 'required|numeric|min:1|max:5',
            'recommendation' => 'required|string|in:pass,extend,fail',
            'comments' => 'nullable|string',
        ]);

        $probation = HcmOnboardingProbation::findOrFail($id);
        $review = $this->probationService->completeProbationReview(
            $probation,
            $request->user()->id,
            $validated
        );

        return response()->json($review);
    }
}
