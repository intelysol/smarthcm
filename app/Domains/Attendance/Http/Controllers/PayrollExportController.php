<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\HcmPayrollTimeExport;
use App\Domains\Attendance\Services\PayrollTimeExportService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollExportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $exports = HcmPayrollTimeExport::where('tenant_id', $tenantId)->get();

        return response()->json(['data' => $exports]);
    }

    public function store(Request $request, PayrollTimeExportService $service): JsonResponse
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'attendance_period_id' => 'nullable|uuid',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $actorId = $request->user()?->id ?? 1;

        $export = $service->generatePayrollExport(
            $tenantId,
            Carbon::parse($validated['period_start']),
            Carbon::parse($validated['period_end']),
            $validated['attendance_period_id'] ?? null,
            $actorId
        );

        return response()->json(['message' => 'Payroll export generated', 'data' => $export], 201);
    }

    public function dispatchExport(string $id, PayrollTimeExportService $service): JsonResponse
    {
        $export = $service->dispatchToIntegrationHub($id);

        return response()->json(['message' => 'Export dispatched to Integration Hub', 'data' => $export]);
    }
}