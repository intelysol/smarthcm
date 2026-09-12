<?php

namespace App\Domains\WorkforceCost\Http\Controllers;

use App\Domains\WorkforceCost\Models\HcmWorkforceCostForecast;
use App\Domains\WorkforceCost\Services\WorkforceCostForecastService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WorkforceCostForecastController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $forecasts = HcmWorkforceCostForecast::where('tenant_id', $tenantId)
            ->orderByDesc('forecast_period_start')
            ->paginate(20);

        return response()->json($forecasts);
    }

    public function generate(Request $request, WorkforceCostForecastService $service): JsonResponse
    {
        $validated = $request->validate([
            'forecast_start' => 'required|date',
            'forecast_end' => 'required|date|after_or_equal:forecast_start',
            'department_id' => 'nullable|uuid',
            'assumptions' => 'nullable|array',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');
        $forecast = $service->generateForecast(
            $tenantId,
            Carbon::parse($validated['forecast_start']),
            Carbon::parse($validated['forecast_end']),
            $validated['department_id'] ?? null,
            $validated['assumptions'] ?? []
        );

        return response()->json(['message' => 'Forecast generated successfully', 'data' => $forecast], 201);
    }
}