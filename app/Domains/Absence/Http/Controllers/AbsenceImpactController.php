<?php

namespace App\Domains\Absence\Http\Controllers;

use App\Domains\Absence\Models\HcmAbsenceOperationalImpact;
use App\Domains\Absence\Services\AbsenceOperationalImpactService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsenceImpactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $query = HcmAbsenceOperationalImpact::where('tenant_id', $tenantId)->with(['replacementEmployee', 'shift']);

        if ($request->filled('coverage_status')) {
            $query->where('coverage_status', $request->input('coverage_status'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function assignReplacement(Request $request, string $id, AbsenceOperationalImpactService $service): JsonResponse
    {
        $validated = $request->validate([
            'replacement_employee_id' => 'required|uuid',
            'replacement_strategy' => 'nullable|string',
        ]);

        $impact = $service->assignReplacement(
            $id,
            $validated['replacement_employee_id'],
            $validated['replacement_strategy'] ?? 'internal_allocation'
        );

        return response()->json(['message' => 'Replacement assigned successfully', 'data' => $impact]);
    }
}