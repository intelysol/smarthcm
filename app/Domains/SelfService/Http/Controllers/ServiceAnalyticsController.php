<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\SelfService\Services\ServiceAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceAnalyticsController extends Controller
{
    public function __construct(
        protected ServiceAnalyticsService $analyticsService
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $summary = $this->analyticsService->getSummaryMetrics($tenantId);

        return response()->json($summary);
    }

    public function byCategory(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $data = $this->analyticsService->getCategoryBreakdown($tenantId);

        return response()->json($data);
    }
}
