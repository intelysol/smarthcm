<?php

declare(strict_types=1);

namespace App\Domains\Operations\Services;

use App\Domains\Operations\Models\OpsIncident;
use Illuminate\Support\Facades\Log;

class IncidentManagementService
{
    /**
     * Create a new operational incident with initial timeline event.
     */
    public function createIncident(
        string $title,
        string $severity,
        string $description,
        ?string $tenantId = null,
        ?int $assigneeId = null,
        string $actor = 'system'
    ): OpsIncident {
        $now = now()->toIso8601String();

        $incident = OpsIncident::query()->create([
            'tenant_id' => $tenantId,
            'title' => $title,
            'severity' => $severity,
            'description' => $description,
            'status' => 'open',
            'assignee_id' => $assigneeId,
            'timeline' => [
                [
                    'event' => 'incident_created',
                    'severity' => $severity,
                    'actor' => $actor,
                    'at' => $now,
                    'note' => 'Incident reported and response initiated.',
                ],
            ],
        ]);

        Log::warning("Operational incident declared [{$incident->id}] [{$severity}]: {$title}", [
            'incident_id' => $incident->id,
            'severity' => $severity,
            'tenant_id' => $tenantId,
        ]);

        return $incident;
    }

    /**
     * Add a timeline event to the incident log.
     */
    public function addTimelineEvent(
        OpsIncident $incident,
        string $event,
        string $actor = 'system',
        ?string $note = null
    ): OpsIncident {
        $timeline = $incident->timeline ?? [];
        $timeline[] = [
            'event' => $event,
            'actor' => $actor,
            'at' => now()->toIso8601String(),
            'note' => $note,
        ];

        $incident->timeline = $timeline;
        $incident->save();

        Log::info("Incident [{$incident->id}] timeline updated: {$event}", [
            'actor' => $actor,
            'note' => $note,
        ]);

        return $incident;
    }

    /**
     * Update incident status with automatic timeline logging.
     */
    public function updateStatus(OpsIncident $incident, string $status, string $actor = 'system'): OpsIncident
    {
        $oldStatus = $incident->status;
        $incident->status = $status;

        $this->addTimelineEvent(
            $incident,
            'status_transition',
            $actor,
            "Status changed from '{$oldStatus}' to '{$status}'"
        );

        return $incident;
    }

    /**
     * Resolve incident, capture root cause, and record timestamp.
     */
    public function resolveIncident(
        OpsIncident $incident,
        string $rootCause,
        string $actor = 'system'
    ): OpsIncident {
        $incident->status = 'resolved';
        $incident->root_cause = $rootCause;
        $incident->resolved_at = now();

        $this->addTimelineEvent(
            $incident,
            'incident_resolved',
            $actor,
            "Incident resolved. Root cause: {$rootCause}"
        );

        Log::notice("Operational incident resolved [{$incident->id}]", [
            'root_cause' => $rootCause,
            'resolved_at' => $incident->resolved_at,
        ]);

        return $incident;
    }
}
