<?php

namespace App\Domains\WorkforceOptimization\Http\Controllers;

use App\Domains\WorkforceOptimization\Services\SkillsOptimizationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillsOptimizationController extends Controller
{
    public function __construct(
        protected SkillsOptimizationService $skillsService
    ) {}

    public function analyze(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        $analysis = $this->skillsService->analyzeSkillsHealth($tenantId);

        return response()->json([
            'success' => true,
            'data' => $analysis,
        ]);
    }

    public function spofs(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        $spofs = $this->skillsService->detectSinglePointsOfFailure($tenantId);

        return response()->json([
            'success' => true,
            'count' => count($spofs),
            'data' => $spofs,
        ]);
    }
}
