<?php

namespace App\Domains\WorkforceProductivity\Http\Controllers;

use App\Domains\WorkforceProductivity\Models\HcmProductivityForecast;
use App\Domains\WorkforceProductivity\Services\ProductivityForecastService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductivityForecastController extends Controller
{
    public function __construct(
        protected ProductivityForecastService $forecastService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        if (! $tenantId) {
            return response()->json(['error' => 'Tenant ID is required'], 400);
        }

        $forecasts = HcmProductivityForecast::with('department')
            ->where('tenant_id', $tenantId)
            ->latest('forecast_end')
            ->paginate(15);

        return response()->json($forecasts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|uuid',
            'department_id' => 'nullable|uuid',
            'forecast_start' => 'required|date',
            'forecast_end' => 'required|date',
            'assumptions' => 'nullable|array',
        ]);

        $forecast = $this->forecastService->generateForecast(
            $validated['tenant_id'],
            $validated['department_id'] ?? null,
            $validated['forecast_start'],
            $validated['forecast_end'],
            $validated['assumptions'] ?? []
        );

        return response()->json(['data' => $forecast], 201);
    }
}
