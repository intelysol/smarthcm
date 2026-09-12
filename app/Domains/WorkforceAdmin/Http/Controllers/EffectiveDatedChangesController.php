<?php

namespace App\Domains\WorkforceAdmin\Http\Controllers;

use App\Domains\WorkforceAdmin\Services\CrossDomainImpactAnalysisService;
use App\Domains\WorkforceAdmin\Services\EffectiveDatedChangeMonitoringService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EffectiveDatedChangesController extends Controller
{
    public function __construct(
        protected EffectiveDatedChangeMonitoringService $monitoringService,
        protected CrossDomainImpactAnalysisService $impactService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID') ?? 'default';
        $changes = $this->monitoringService->getEffectiveDatedChanges($tenantId);

        return response()->json($changes);
    }

    public function impactAnalysis(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'operation_type' => 'required|string|max:80',
            'proposed_changes' => 'required|array',
            'employee_id' => 'nullable|uuid',
        ]);

        $impact = $this->impactService->analyzeImpact(
            $validated['operation_type'],
            $validated['proposed_changes'],
            $validated['employee_id'] ?? null
        );

        return response()->json($impact);
    }
}
