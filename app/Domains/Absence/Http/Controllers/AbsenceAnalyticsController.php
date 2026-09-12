<?php

namespace App\Domains\Absence\Http\Controllers;

use App\Domains\Absence\Models\HcmAbsenceForecast;
use App\Domains\Absence\Services\AbsenceAnalyticsAndForecastingService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsenceAnalyticsController extends Controller
{
    public function rates(Request $request, AbsenceAnalyticsAndForecastingService $service): JsonResponse
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'department_id' => 'nullable|uuid',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');

        $metrics = $service->calculateAbsenceRate(
            $tenantId,
            Carbon::parse($validated['period_start']),
            Carbon::parse($validated['period_end']),
            $validated['department_id'] ?? null
        );

        return response()->json(['data' => $metrics]);
    }

    public function forecasts(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $forecasts = HcmAbsenceForecast::where('tenant_id', $tenantId)->get();

        return response()->json(['data' => $forecasts]);
    }

    public function generateForecast(Request $request, AbsenceAnalyticsAndForecastingService $service): JsonResponse
    {
        $validated = $request->validate([
            'forecast_period_start' => 'required|date',
            'forecast_period_end' => 'required|date',
            'department_id' => 'nullable|uuid',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');

        $forecast = $service->generateForecast(
            $tenantId,
            Carbon::parse($validated['forecast_period_start']),
            Carbon::parse($validated['forecast_period_end']),
            $validated['department_id'] ?? null
        );

        return response()->json(['message' => 'Absence forecast generated', 'data' => $forecast], 201);
    }
}