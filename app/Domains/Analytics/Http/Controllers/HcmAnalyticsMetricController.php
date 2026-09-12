<?php

namespace App\Domains\Analytics\Http\Controllers;

use App\Domains\Analytics\Models\HcmAnalyticsMetric;
use App\Domains\Analytics\Requests\CreateHcmMetricRequest;
use App\Domains\Analytics\Services\HcmMetricRegistryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HcmAnalyticsMetricController extends Controller
{
    public function __construct(
        protected HcmMetricRegistryService $metricService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $category = $request->input('category');
        $metrics = $this->metricService->getCatalog($tenantId, $category);

        if ($request->wantsJson()) {
            return response()->json($metrics);
        }

        return view('analytics.metrics.index', compact('metrics', 'category'));
    }

    public function show(Request $request, HcmAnalyticsMetric $metric): JsonResponse
    {
        $metric->load(['versions', 'targets', 'alerts']);
        return response()->json($metric);
    }

    public function store(CreateHcmMetricRequest $request): JsonResponse
    {
        $user = $request->user();
        $metric = $this->metricService->createMetric($user->tenant_id, $request->validated(), $user);

        return response()->json([
            'message' => 'HCM Metric registered successfully.',
            'data' => $metric,
        ], 201);
    }
}
