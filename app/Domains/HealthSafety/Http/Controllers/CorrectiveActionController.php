<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Http\Controllers;

use App\Domains\HealthSafety\Models\HcmSafetyIncidentAction;
use App\Domains\HealthSafety\Services\CorrectiveActionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CorrectiveActionController extends Controller
{
    public function __construct(
        protected CorrectiveActionService $service
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'incident_id' => 'required|uuid',
            'action_type' => 'required|in:corrective,preventive,immediate_containment',
            'hierarchy_level' => 'required|in:elimination,substitution,engineering_controls,administrative_controls,ppe',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to_id' => 'nullable|uuid',
            'due_date' => 'required|date',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $action = $this->service->createAction($validated, $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Corrective action (CAPA) created successfully.',
            'data' => $action,
        ], 201);
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        $action = HcmSafetyIncidentAction::findOrFail($id);

        $validated = $request->validate([
            'completion_notes' => 'required|string',
        ]);

        $updated = $this->service->completeAction($action, $validated['completion_notes'], (int) $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Action marked as completed and queued for verification.',
            'data' => $updated,
        ]);
    }

    public function verify(Request $request, string $id): JsonResponse
    {
        $action = HcmSafetyIncidentAction::findOrFail($id);

        $validated = $request->validate([
            'is_effective' => 'required|boolean',
            'verification_notes' => 'required|string',
        ]);

        $updated = $this->service->verifyAction(
            $action,
            (bool) $validated['is_effective'],
            $validated['verification_notes'],
            (int) $request->user()?->id
        );

        return response()->json([
            'status' => 'success',
            'message' => $validated['is_effective'] ? 'Action verified and closed.' : 'Action failed verification and re-opened.',
            'data' => $updated,
        ]);
    }
}
