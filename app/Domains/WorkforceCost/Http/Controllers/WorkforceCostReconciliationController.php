<?php

namespace App\Domains\WorkforceCost\Http\Controllers;

use App\Domains\WorkforceCost\Models\HcmWorkforceCostReconciliation;
use App\Domains\WorkforceCost\Services\WorkforceCostReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WorkforceCostReconciliationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $reconciliations = HcmWorkforceCostReconciliation::where('tenant_id', $tenantId)
            ->orderByDesc('reconciled_at')
            ->paginate(20);

        return response()->json($reconciliations);
    }

    public function reconcilePayroll(Request $request, WorkforceCostReconciliationService $service): JsonResponse
    {
        $validated = $request->validate([
            'payroll_run_id' => 'required|uuid',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $result = $service->reconcileWithPayroll($tenantId, $validated['payroll_run_id']);

        return response()->json(['message' => 'Payroll reconciliation executed successfully', 'data' => $result], 201);
    }
}