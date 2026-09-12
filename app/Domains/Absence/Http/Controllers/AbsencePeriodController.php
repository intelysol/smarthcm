<?php

namespace App\Domains\Absence\Http\Controllers;

use App\Domains\Absence\Models\HcmAbsencePeriod;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsencePeriodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $query = HcmAbsencePeriod::where('tenant_id', $tenantId)->with(['returnPlan', 'cases']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }
        if ($request->filled('is_long_term')) {
            $query->where('is_long_term', $request->boolean('is_long_term'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function extend(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'new_expected_return_date' => 'required|date|after:today',
        ]);

        $period = HcmAbsencePeriod::findOrFail($id);
        $period->update([
            'expected_return_date' => $validated['new_expected_return_date'],
            'status' => 'extended',
        ]);

        return response()->json(['message' => 'Absence period extended', 'data' => $period]);
    }

    public function recordReturn(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'actual_return_date' => 'required|date',
        ]);

        $period = HcmAbsencePeriod::findOrFail($id);
        $period->update([
            'actual_return_date' => $validated['actual_return_date'],
            'status' => 'returned',
        ]);

        return response()->json(['message' => 'Employee return recorded', 'data' => $period]);
    }
}