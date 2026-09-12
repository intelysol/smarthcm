<?php

namespace App\Domains\WorkforceProductivity\Http\Controllers;

use App\Domains\WorkforceProductivity\Services\AdvisoryWorkforceProductivityAiService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvisoryProductivityAiController extends Controller
{
    public function __construct(
        protected AdvisoryWorkforceProductivityAiService $aiService
    ) {}

    public function insights(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        $departmentId = $request->query('department_id');

        if (! $tenantId) {
            return response()->json(['error' => 'Tenant ID is required'], 400);
        }

        $insights = $this->aiService->generateInsights($tenantId, $departmentId);
        return response()->json($insights);
    }
}
