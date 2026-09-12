<?php

namespace App\Domains\Mobility\Services;

use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityChange;
use App\Models\User;
use App\Domains\Shared\Services\AuditService;

class MobilityChangeService
{
    public function __construct(
        protected MobilityAssignmentService $assignmentService,
        protected AuditService $auditService
    ) {}

    /**
     * Request an in-flight assignment change (e.g. host position, host manager, cost center).
     */
    public function requestChange(MobilityAssignment $assignment, array $data, ?User $actor = null): MobilityChange
    {
        $change = MobilityChange::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'change_type' => $data['change_type'],
            'previous_values' => $data['previous_values'] ?? [],
            'proposed_values' => $data['proposed_values'] ?? [],
            'effective_date' => $data['effective_date'] ?? now()->toDateString(),
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        $this->auditService->record(
            tenantId: $assignment->tenant_id,
            eventType: 'assignment_change_requested',
            action: 'create',
            entityType: 'MobilityChange',
            entityId: $change->id,
            actorId: $actor?->id ? (int) $actor->id : null,
            before: null,
            after: $change->toArray()
        );

        return $change;
    }

    /**
     * Approve and execute the assignment change.
     */
    public function approveChange(MobilityChange $change, User $actor): MobilityChange
    {
        $change->update([
            'status' => 'approved',
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        $assignment = $change->assignment;
        $before = $assignment->toArray();

        // Apply proposed values to assignment
        if (!empty($change->proposed_values)) {
            $assignment->fill($change->proposed_values);
            $assignment->current_version += 1;
            $assignment->save();
        }

        $change->update(['status' => 'executed']);

        $this->assignmentService->createVersionSnapshot(
            $assignment,
            "Change executed: {$change->change_type}",
            $actor
        );

        $this->auditService->record(
            tenantId: $assignment->tenant_id,
            eventType: 'assignment_change_executed',
            action: 'execute',
            entityType: 'MobilityChange',
            entityId: $change->id,
            actorId: (int) $actor->id,
            before: $before,
            after: $assignment->fresh()->toArray()
        );

        return $change;
    }
}
