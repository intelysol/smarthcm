<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\HcmPayrollTimeReconciliation;
use App\Domains\Attendance\Services\PayrollTimeReconciliationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollReconciliationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $reconciliations = HcmPayrollTimeReconciliation::where('tenant_id', $tenantId)->get();

        return response()->json(['data' => $reconciliations]);
    }

    public function store(Request $request, PayrollTimeReconciliationService $service): JsonResponse
    {
        $validated = $request->validate([
            'payroll_time_export_id' => 'required|uuid',
            'payroll_processed_records' => 'required|array',
            'payroll_batch_id' => 'nullable|string',
        ]);

        $actorId = $request->user()?->id ?? 1;

        $reconciliation = $service->reconcileExportAgainstPayrollRecords(
            $validated['payroll_time_export_id'],
            $validated['payroll_processed_records'],
            $validated['payroll_batch_id'] ?? null,
            $actorId
        );

        return response()->json(['message' => 'Reconciliation completed', 'data' => $reconciliation], 201);
    }
}