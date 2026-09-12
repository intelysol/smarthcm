<?php

namespace App\Domains\WorkforceProductivity\Http\Controllers;

use App\Domains\WorkforceProductivity\DTOs\ProductivityMeasurementData;
use App\Domains\WorkforceProductivity\Models\HcmProductivityMeasurement;
use App\Domains\WorkforceProductivity\Services\ProductivityMeasurementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductivityMeasurementController extends Controller
{
    public function __construct(
        protected ProductivityMeasurementService $measurementService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        if (! $tenantId) {
            return response()->json(['error' => 'Tenant ID is required'], 400);
        }

        $query = HcmProductivityMeasurement::with(['metricDefinition', 'department', 'lines'])
            ->where('tenant_id', $tenantId);

        if ($request->has('department_id')) {
            $query->where('department_id', $request->query('department_id'));
        }
        if ($request->has('period_name')) {
            $query->where('period_name', $request->query('period_name'));
        }

        $measurements = $query->latest('period_end')->paginate(20);
        return response()->json($measurements);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|uuid',
            'metric_definition_id' => 'required|uuid',
            'metric_version_id' => 'nullable|uuid',
            'department_id' => 'nullable|uuid',
            'location_id' => 'nullable|uuid',
            'shift_id' => 'nullable|uuid',
            'employee_id' => 'nullable|uuid',
            'period_type' => 'nullable|string',
            'period_name' => 'required|string',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'output_volume' => 'required|numeric',
            'labor_hours' => 'required|numeric',
            'productive_hours' => 'required|numeric',
            'scheduled_hours' => 'nullable|numeric',
            'available_hours' => 'nullable|numeric',
            'overtime_hours' => 'nullable|numeric',
            'idle_hours' => 'nullable|numeric',
            'quality_rate' => 'nullable|numeric',
            'labor_cost' => 'nullable|numeric',
            'idempotency_key' => 'nullable|string',
        ]);

        $dto = new ProductivityMeasurementData(
            tenantId: $validated['tenant_id'],
            metricDefinitionId: $validated['metric_definition_id'],
            metricVersionId: $validated['metric_version_id'] ?? null,
            departmentId: $validated['department_id'] ?? null,
            locationId: $validated['location_id'] ?? null,
            shiftId: $validated['shift_id'] ?? null,
            employeeId: $validated['employee_id'] ?? null,
            periodType: $validated['period_type'] ?? 'monthly',
            periodName: $validated['period_name'],
            periodStart: $validated['period_start'],
            periodEnd: $validated['period_end'],
            outputVolume: (float) $validated['output_volume'],
            laborHours: (float) $validated['labor_hours'],
            productiveHours: (float) $validated['productive_hours'],
            scheduledHours: (float) ($validated['scheduled_hours'] ?? 0.0),
            availableHours: (float) ($validated['available_hours'] ?? 0.0),
            overtimeHours: (float) ($validated['overtime_hours'] ?? 0.0),
            idleHours: (float) ($validated['idle_hours'] ?? 0.0),
            qualityRate: isset($validated['quality_rate']) ? (float) $validated['quality_rate'] : null,
            laborCost: (float) ($validated['labor_cost'] ?? 0.0),
            idempotencyKey: $validated['idempotency_key'] ?? null
        );

        $measurement = $this->measurementService->calculateMeasurement($dto, $request->user()?->id);
        return response()->json(['data' => $measurement], 201);
    }

    public function recordOutput(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|uuid',
            'output_date' => 'required|date',
            'metric_definition_id' => 'nullable|uuid',
            'employee_id' => 'nullable|uuid',
            'department_id' => 'nullable|uuid',
            'units_completed' => 'required|numeric',
            'units_defective' => 'nullable|numeric',
            'rework_count' => 'nullable|numeric',
            'revenue_generated' => 'nullable|numeric',
            'quality_score' => 'nullable|numeric',
            'source_domain' => 'nullable|string',
        ]);

        $record = $this->measurementService->recordOutput($validated);
        return response()->json(['data' => $record], 201);
    }

    public function recordTime(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|uuid',
            'record_date' => 'required|date',
            'employee_id' => 'nullable|uuid',
            'department_id' => 'nullable|uuid',
            'category' => 'required|string',
            'hours' => 'nullable|numeric',
            'minutes' => 'nullable|integer',
            'nature' => 'nullable|string',
        ]);

        $record = $this->measurementService->recordTime($validated);
        return response()->json(['data' => $record], 201);
    }
}
