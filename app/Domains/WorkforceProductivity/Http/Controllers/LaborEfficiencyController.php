<?php

namespace App\Domains\WorkforceProductivity\Http\Controllers;

use App\Domains\WorkforceProductivity\Services\LaborEfficiencyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaborEfficiencyController extends Controller
{
    public function __construct(
        protected LaborEfficiencyService $efficiencyService
    ) {}

    public function efficiency(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'output_volume' => 'required|numeric',
            'total_labor_hours' => 'required|numeric',
            'productive_hours' => 'required|numeric',
            'paid_hours' => 'required|numeric',
            'overtime_hours' => 'required|numeric',
            'total_fte' => 'required|numeric',
            'total_workforce_cost' => 'required|numeric',
        ]);

        $result = $this->efficiencyService->computeEfficiency(
            (float) $validated['output_volume'],
            (float) $validated['total_labor_hours'],
            (float) $validated['productive_hours'],
            (float) $validated['paid_hours'],
            (float) $validated['overtime_hours'],
            (float) $validated['total_fte'],
            (float) $validated['total_workforce_cost']
        );

        return response()->json(['data' => $result->toArray()]);
    }
}
