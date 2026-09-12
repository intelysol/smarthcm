<?php

namespace App\Domains\Onboarding\Http\Controllers;

use App\Domains\Onboarding\Services\OnboardingAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingAnalyticsController extends Controller
{
    public function __construct(protected OnboardingAnalyticsService $analyticsService)
    {
    }

    public function kpis(Request $request): JsonResponse
    {
        $kpis = $this->analyticsService->getOnboardingKpis($request->user()->tenant_id);
        return response()->json($kpis);
    }
}
