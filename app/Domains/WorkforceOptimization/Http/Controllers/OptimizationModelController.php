<?php

namespace App\Domains\WorkforceOptimization\Http\Controllers;

use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationModel;
use App\Domains\WorkforceOptimization\Services\OptimizationModelService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OptimizationModelController extends Controller
{
    public function __construct(
        protected OptimizationModelService $modelService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        $models = $this->modelService->getActiveModels($tenantId);

        return response()->json([
            'success' => true,
            'data' => $models,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model_code' => 'required|string|max:64',
            'name' => 'required|string|max:150',
            'scope_type' => 'nullable|string',
            'planning_horizon' => 'nullable|string',
        ]);

        $tenantId = $request->header('X-Tenant-ID') ?? $request->input('tenant_id');
        $validated['tenant_id'] = $tenantId;

        $model = $this->modelService->createModel($validated);

        return response()->json([
            'success' => true,
            'data' => $model,
        ], 201);
    }
}
