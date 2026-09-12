<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Services;

use App\Domains\HealthSafety\Models\HcmSafetyIncident;
use App\Domains\HealthSafety\Models\HcmSafetyIncidentInvestigation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SafetyInvestigationService
{
    /**
     * Start an investigation for an incident.
     */
    public function startInvestigation(array $data, ?int $userId = null): HcmSafetyIncidentInvestigation
    {
        return DB::transaction(function () use ($data, $userId) {
            $incident = HcmSafetyIncident::findOrFail($data['incident_id']);

            $investigation = HcmSafetyIncidentInvestigation::create([
                'tenant_id' => $incident->tenant_id,
                'safety_incident_id' => $incident->id,
                'lead_investigator_id' => $userId,
                'status' => HcmSafetyIncidentInvestigation::STATUS_ASSIGNED,
                'started_at' => $data['started_at'] ?? Carbon::now()->toDateString(),
                'root_cause_category' => $data['methodology'] ?? 'human_factor',
                'findings' => $data['summary_of_findings'] ?? 'Under investigation',
                'contributing_factors' => is_array($data['contributing_factors'] ?? null)
                    ? implode(', ', $data['contributing_factors'])
                    : ($data['contributing_factors'] ?? null),
            ]);

            $incident->update([
                'status' => HcmSafetyIncident::STATUS_UNDER_INVESTIGATION,
            ]);

            return $investigation;
        });
    }

    /**
     * Update investigation findings and root causes.
     */
    public function updateFindings(
        HcmSafetyIncidentInvestigation $investigation,
        array $findings,
        int $userId
    ): HcmSafetyIncidentInvestigation {
        $investigation->update([
            'findings' => $findings['summary_of_findings'] ?? $investigation->findings,
            'root_cause_analysis' => is_array($findings['root_causes'] ?? null)
                ? implode('; ', $findings['root_causes'])
                : ($findings['root_causes'] ?? $investigation->root_cause_analysis),
            'contributing_factors' => is_array($findings['contributing_factors'] ?? null)
                ? implode(', ', $findings['contributing_factors'])
                : ($findings['contributing_factors'] ?? $investigation->contributing_factors),
            'status' => HcmSafetyIncidentInvestigation::STATUS_IN_PROGRESS,
        ]);

        return $investigation->fresh();
    }

    /**
     * Complete and conclude an investigation.
     */
    public function completeInvestigation(
        HcmSafetyIncidentInvestigation $investigation,
        array $conclusion,
        int $userId
    ): HcmSafetyIncidentInvestigation {
        $rootAnalysis = is_array($conclusion['root_causes'] ?? null)
            ? implode("; ", $conclusion['root_causes'])
            : ($conclusion['root_causes'] ?? $investigation->root_cause_analysis);

        if (empty($rootAnalysis)) {
            throw ValidationException::withMessages([
                'root_causes' => ['At least one root cause must be identified to complete the investigation.'],
            ]);
        }

        return DB::transaction(function () use ($investigation, $conclusion, $rootAnalysis, $userId) {
            $investigation->update([
                'status' => HcmSafetyIncidentInvestigation::STATUS_COMPLETED,
                'completed_at' => Carbon::now()->toDateString(),
                'findings' => $conclusion['summary_of_findings'] ?? $investigation->findings,
                'root_cause_analysis' => $rootAnalysis,
                'approved_by' => $userId,
                'approved_at' => Carbon::now(),
            ]);

            // Sync root cause summary onto the incident
            $investigation->incident->update([
                'status' => HcmSafetyIncident::STATUS_ACTION_PENDING,
                'description' => $investigation->incident->description . "\n[Root cause: {$rootAnalysis}]",
            ]);

            $investigation->incident->root_cause_summary = $rootAnalysis;

            return $investigation->fresh();
        });
    }
}

