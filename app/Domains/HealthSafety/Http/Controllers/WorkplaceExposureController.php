<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Http\Controllers;

use App\Domains\HealthSafety\Services\WorkplaceExposureService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkplaceExposureController extends Controller
{
    public function __construct(
        protected WorkplaceExposureService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $filters = $request->only([
            'hazard_type', 'substance_or_agent', 'threshold_exceeded',
            'date_from', 'date_to',
        ]);

        $exposures = $this->service->listExposures($tenantId, $filters, (int) $request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $exposures,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|uuid',
            'employee_id' => 'required|uuid',
            'hazard_type' => 'required|in:chemical,biological,noise,radiation,ergonomic,heat_cold_stress,airborne_particulate,other',
            'substance_or_agent' => 'required|string|max:255',
            'exposure_date' => 'required|date',
            'location_id' => 'nullable|uuid',
            'duration_minutes' => 'nullable|integer',
            'measured_level' => 'nullable|numeric',
            'unit_of_measure' => 'nullable|string|max:50',
            'exposure_limit_threshold' => 'nullable|numeric',
            'threshold_exceeded' => 'nullable|boolean',
            'ppe_used' => 'nullable|string',
            'control_measures_in_place' => 'nullable|string',
            'medical_surveillance_required' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $request->user()?->tenant_id ?? 'default';

        $exposure = $this->service->recordExposure($validated, $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Workplace exposure recorded successfully.',
            'data' => $exposure,
        ], 201);
    }

    public function employeeExposures(Request $request, string $employeeId): JsonResponse
    {
        $exposures = $this->service->getEmployeeExposures($employeeId);

        return response()->json([
            'status' => 'success',
            'data' => $exposures,
        ]);
    }
}
