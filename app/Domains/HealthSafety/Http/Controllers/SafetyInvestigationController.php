<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Http\Controllers;

use App\Domains\HealthSafety\Models\HcmSafetyIncidentInvestigation;
use App\Domains\HealthSafety\Services\SafetyInvestigationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SafetyInvestigationController extends Controller
{
    public function __construct(
        protected SafetyInvestigationService $service
    ) {}

    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'incident_id' => 'required|uuid',
            'lead_investigator_id' => 'required|uuid',
            'team_members' => 'nullable|array',
            'methodology' => 'nullable|string|max:255',
            'summary_of_findings' => 'nullable|string',
        ]);

        $investigation = $this->service->startInvestigation($validated, $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Safety investigation initiated.',
            'data' => $investigation,
        ], 201);
    }

    public function updateFindings(Request $request, string $id): JsonResponse
    {
        $investigation = HcmSafetyIncidentInvestigation::findOrFail($id);

        $validated = $request->validate([
            'summary_of_findings' => 'nullable|string',
            'root_causes' => 'nullable|array',
            'contributing_factors' => 'nullable|array',
            'methodology' => 'nullable|string|max:255',
        ]);

        $updated = $this->service->updateFindings($investigation, $validated, (int) $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Investigation findings updated.',
            'data' => $updated,
        ]);
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        $investigation = HcmSafetyIncidentInvestigation::findOrFail($id);

        $validated = $request->validate([
            'summary_of_findings' => 'required|string',
            'root_causes' => 'required|array|min:1',
            'contributing_factors' => 'nullable|array',
        ]);

        $completed = $this->service->completeInvestigation($investigation, $validated, (int) $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Safety investigation completed and root causes established.',
            'data' => $completed,
        ]);
    }
}
