<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\BenefitLifeEvent;
use App\Domains\Benefits\Services\BenefitLifeEventService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitLifeEventController extends Controller
{
    public function __construct(
        protected BenefitLifeEventService $lifeEventService
    ) {}

    public function types(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $types = $this->lifeEventService->getEventTypes($tenantId);

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    public function storeType(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:60',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'notification_window_days' => 'nullable|integer',
            'election_window_days' => 'nullable|integer',
            'documentation_deadline_days' => 'nullable|integer',
            'requires_document' => 'nullable|boolean',
            'effective_date_rule' => 'nullable|string',
        ]);

        $tenantId = $request->user()?->tenant_id ?? (string) $request->header('X-Tenant-ID');
        $type = $this->lifeEventService->createEventType($tenantId, $validated, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Life event type created.',
            'data' => $type,
        ], 201);
    }

    public function employeeEvents(Employee $employee): JsonResponse
    {
        $events = $this->lifeEventService->getLifeEventsForEmployee($employee);

        return response()->json([
            'success' => true,
            'employee_id' => $employee->id,
            'data' => $events,
        ]);
    }

    public function report(Employee $employee, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'life_event_type_id' => 'nullable|uuid|exists:benefit_life_event_types,id',
            'event_type' => 'nullable|string|max:40',
            'event_date' => 'required|date',
            'description' => 'nullable|string',
            'document_id' => 'nullable|uuid',
            'affected_plans' => 'nullable|array',
        ]);

        $lifeEvent = $this->lifeEventService->reportLifeEvent($employee, $validated, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Qualifying life event reported successfully.',
            'data' => $lifeEvent->load('lifeEventType'),
        ], 201);
    }

    public function verify(BenefitLifeEvent $event, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'approved' => 'required|boolean',
            'rejection_reason' => 'nullable|string',
        ]);

        $verified = $this->lifeEventService->verifyLifeEvent(
            $event,
            $request->user(),
            $validated['approved'],
            $validated['rejection_reason'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => $validated['approved'] ? 'Life event verified and eligibility recalculated.' : 'Life event rejected.',
            'data' => $verified,
        ]);
    }
}
