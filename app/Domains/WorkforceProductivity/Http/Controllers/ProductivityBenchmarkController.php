<?php

namespace App\Domains\WorkforceProductivity\Http\Controllers;

use App\Domains\WorkforceProductivity\Models\HcmProductivityBenchmark;
use App\Domains\WorkforceProductivity\Services\ProductivityBenchmarkService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductivityBenchmarkController extends Controller
{
    public function __construct(
        protected ProductivityBenchmarkService $benchmarkService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        if (! $tenantId) {
            return response()->json(['error' => 'Tenant ID is required'], 400);
        }

        $benchmarks = HcmProductivityBenchmark::where('tenant_id', $tenantId)
            ->latest()
            ->paginate(15);

        return response()->json($benchmarks);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|uuid',
            'benchmark_name' => 'required|string|max:120',
            'benchmark_type' => 'required|string',
            'baseline_period_start' => 'required|date',
            'baseline_period_end' => 'required|date',
            'baseline_productivity_rate' => 'required|numeric',
            'comparison_period_start' => 'required|date',
            'comparison_period_end' => 'required|date',
            'comparison_productivity_rate' => 'required|numeric',
            'benchmark_data' => 'nullable|array',
        ]);

        $benchmark = $this->benchmarkService->createBenchmark(
            tenantId: $validated['tenant_id'],
            benchmarkName: $validated['benchmark_name'],
            benchmarkType: $validated['benchmark_type'],
            baseStart: $validated['baseline_period_start'],
            baseEnd: $validated['baseline_period_end'],
            baseRate: (float) $validated['baseline_productivity_rate'],
            compStart: $validated['comparison_period_start'],
            compEnd: $validated['comparison_period_end'],
            compRate: (float) $validated['comparison_productivity_rate'],
            extraData: $validated['benchmark_data'] ?? []
        );

        return response()->json(['data' => $benchmark], 201);
    }
}
