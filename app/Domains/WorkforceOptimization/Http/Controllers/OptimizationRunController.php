<?php

namespace App\Domains\WorkforceOptimization\Http\Controllers;

use App\Domains\WorkforceOptimization\Contracts\WorkforceOptimizationInterface;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRun;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OptimizationRunController extends Controller
{
    public function __construct(
        protected WorkforceOptimizationInterface $optimizationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        $query = HcmWorkforceOptimizationRun::with(['model', 'modelVersion']);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate(15),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $run = HcmWorkforceOptimizationRun::with(['model', 'modelVersion', 'recommendations.factors', 'scenarios'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $run,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->input('tenant_id') ?? '00000000-0000-0000-0000-000000000001';
        $modelCode = $request->input('model_code');
        $scope = $request->input('scope', []);

        $run = $this->optimizationService->runOptimization(
            tenantId: $tenantId,
            modelCode: $modelCode,
            scope: $scope,
            triggeredBy: $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Workforce optimization run completed successfully.',
            'data' => $run,
        ], 201);
    }
}
