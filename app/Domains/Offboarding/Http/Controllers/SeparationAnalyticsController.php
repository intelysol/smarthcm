<?php

namespace App\Domains\Offboarding\Http\Controllers;

use App\Domains\Offboarding\Services\SeparationAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeparationAnalyticsController extends Controller
{
    public function __construct(protected SeparationAnalyticsService $analyticsService)
    {
    }

    public function metrics(Request $request): JsonResponse
    {
        $metrics = $this->analyticsService->getOffboardingMetrics($request->user()->tenant_id);
        return response()->json($metrics);
    }
}
