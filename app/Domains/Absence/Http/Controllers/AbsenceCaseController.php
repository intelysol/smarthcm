<?php

namespace App\Domains\Absence\Http\Controllers;

use App\Domains\Absence\Models\HcmAbsenceCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AbsenceCaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $cases = HcmAbsenceCase::where('tenant_id', $tenantId)->get();

        return response()->json(['data' => $cases]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'absence_period_id' => 'nullable|uuid',
            'case_type' => 'nullable|string',
            'severity' => 'nullable|string',
            'trigger_reason' => 'required|string',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $hrUserId = $request->user()?->id;

        $case = HcmAbsenceCase::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'case_number' => 'ABS-CASE-' . strtoupper(Str::random(8)),
            'employee_id' => $validated['employee_id'],
            'absence_period_id' => $validated['absence_period_id'] ?? null,
            'case_type' => $validated['case_type'] ?? 'long_term_absence',
            'severity' => $validated['severity'] ?? 'standard',
            'status' => 'opened',
            'assigned_hr_user_id' => $hrUserId,
            'trigger_reason' => $validated['trigger_reason'],
            'opened_at' => now(),
        ]);

        return response()->json(['message' => 'Absence case opened', 'data' => $case], 201);
    }
}