<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Services\BenefitsAdministrationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitsAdministrationController extends Controller
{
    public function __construct(
        protected BenefitsAdministrationService $adminService
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $metrics = $this->adminService->getDashboardMetrics($tenantId);

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    public function bulkOperation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'operation' => 'required|string|in:bulk_eligibility_evaluation',
            'dry_run' => 'nullable|boolean',
            'params' => 'nullable|array',
        ]);

        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $dryRun = (bool) ($validated['dry_run'] ?? true);

        $result = $this->adminService->executeBulkOperation(
            $tenantId,
            $validated['operation'],
            $validated['params'] ?? [],
            $dryRun,
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
