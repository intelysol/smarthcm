<?php

namespace App\Domains\Absence\Http\Controllers;

use App\Domains\Absence\Models\HcmAbsenceReconciliation;
use App\Domains\Absence\Services\AbsenceReconciliationService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsenceReconciliationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $records = HcmAbsenceReconciliation::where('tenant_id', $tenantId)->get();

        return response()->json(['data' => $records]);
    }

    public function run(Request $request, AbsenceReconciliationService $service): JsonResponse
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $userId = $request->user()?->id;

        $rec = $service->reconcilePeriod(
            $tenantId,
            Carbon::parse($validated['period_start']),
            Carbon::parse($validated['period_end']),
            $userId
        );

        return response()->json(['message' => 'Absence reconciliation completed', 'data' => $rec], 201);
    }
}