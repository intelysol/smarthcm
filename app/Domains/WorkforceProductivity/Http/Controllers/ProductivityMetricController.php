<?php

namespace App\Domains\WorkforceProductivity\Http\Controllers;

use App\Domains\WorkforceProductivity\Models\HcmProductivityMetricDefinition;
use App\Domains\WorkforceProductivity\Services\ProductivityMetricService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductivityMetricController extends Controller
{
    public function __construct(
        protected ProductivityMetricService $metricService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        if (! $tenantId) {
            return response()->json(['error' => 'Tenant ID is required'], 400);
        }

        $metrics = $this->metricService->listMetrics($tenantId);
        return response()->json(['data' => $metrics]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|uuid',
            'code' => 'required|string|max:64',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'metric_type' => 'nullable|string',
            'unit' => 'nullable|string',
            'dimensions' => 'nullable|array',
            'formula_name' => 'nullable|string',
            'numerator_code' => 'nullable|string',
            'denominator_code' => 'nullable|string',
            'calculation_expression' => 'nullable|string',
            'effective_from' => 'nullable|date',
        ]);

        $metric = $this->metricService->createMetric($validated, $request->user()?->id);
        return response()->json(['data' => $metric], 201);
    }

    public function createVersion(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'formula_name' => 'nullable|string',
            'numerator_code' => 'required|string',
            'denominator_code' => 'required|string',
            'calculation_expression' => 'nullable|string',
            'effective_from' => 'nullable|date',
            'change_notes' => 'nullable|string',
        ]);

        $version = $this->metricService->createNewVersion($id, $validated, $request->user()?->id);
        return response()->json(['data' => $version], 201);
    }
}
