<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Http\Controllers;

use App\Domains\HealthSafety\Models\HcmSafetyIncident;
use App\Domains\HealthSafety\Services\SafetyIncidentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SafetyIncidentController extends Controller
{
    public function __construct(
        protected SafetyIncidentService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $filters = $request->only([
            'company_id', 'status', 'severity_level', 'incident_type',
            'date_from', 'date_to', 'is_osha_recordable',
        ]);

        $incidents = $this->service->listIncidents($tenantId, $filters, (int) $request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $incidents,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|uuid',
            'incident_type' => 'required|in:injury,near_miss,hazard_unsafe_condition,illness,property_damage,environmental_spill,security',
            'severity_level' => 'required|in:minor,moderate,severe,critical,fatal',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'incident_date' => 'required|date',
            'incident_time' => 'nullable|string',
            'location_id' => 'nullable|uuid',
            'department_id' => 'nullable|uuid',
            'exact_location_details' => 'nullable|string',
            'affected_employee_id' => 'nullable|uuid',
            'reporter_employee_id' => 'nullable|uuid',
            'supervisor_employee_id' => 'nullable|uuid',
            'is_osha_recordable' => 'boolean',
            'is_lost_time' => 'boolean',
            'lost_work_days' => 'nullable|integer',
            'restricted_work_days' => 'nullable|integer',
            'medical_treatment_required' => 'boolean',
            'immediate_actions_taken' => 'nullable|string',
            'witnesses' => 'nullable|array',
        ]);

        $validated['tenant_id'] = $request->user()?->tenant_id ?? 'default';

        $incident = $this->service->reportIncident($validated, $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Safety incident reported successfully.',
            'data' => $incident,
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $incident = HcmSafetyIncident::with([
            'affectedEmployee', 'location', 'department', 'witnesses',
            'investigations.leadInvestigator', 'actions.assignee',
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $incident,
        ]);
    }

    public function escalate(Request $request, string $id): JsonResponse
    {
        $incident = HcmSafetyIncident::findOrFail($id);

        $validated = $request->validate([
            'severity_level' => 'required|in:minor,moderate,severe,critical,fatal',
            'reason' => 'required|string',
        ]);

        $updated = $this->service->escalateSeverity(
            $incident,
            $validated['severity_level'],
            $validated['reason'],
            (int) $request->user()?->id
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Incident severity updated and notifications dispatched.',
            'data' => $updated,
        ]);
    }

    public function close(Request $request, string $id): JsonResponse
    {
        $incident = HcmSafetyIncident::findOrFail($id);

        $validated = $request->validate([
            'closure_notes' => 'nullable|string',
        ]);

        $closed = $this->service->closeIncident($incident, $validated['closure_notes'] ?? null, (int) $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Incident successfully closed.',
            'data' => $closed,
        ]);
    }
}
