<?php

namespace App\Domains\Lifecycle\Http\Controllers;

use App\Domains\Lifecycle\Services\PersonnelActionAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonnelActionAnalyticsController extends Controller
{
    public function __construct(protected PersonnelActionAnalyticsService $analyticsService)
    {
    }

    public function metrics(Request $request): JsonResponse
    {
        $metrics = $this->analyticsService->getLifecycleMetrics($request->user()->tenant_id);
        return response()->json($metrics);
    }
}
