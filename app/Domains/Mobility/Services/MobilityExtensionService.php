<?php

namespace App\Domains\Mobility\Services;

use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityExtension;
use App\Models\User;
use App\Domains\Shared\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MobilityExtensionService
{
    public function __construct(
        protected MobilityAssignmentService $assignmentService,
        protected AuditService $auditService
    ) {}

    /**
     * Request an extension for an active assignment.
     */
    public function requestExtension(MobilityAssignment $assignment, array $data, ?User $actor = null): MobilityExtension
    {
        $extensionNumber = 'EXT-' . strtoupper(Str::random(8));

        $extension = MobilityExtension::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'extension_number' => $extensionNumber,
            'current_end_date' => $assignment->planned_end_date,
            'proposed_end_date' => $data['proposed_end_date'],
            'extension_reason' => $data['extension_reason'],
            'additional_estimated_cost' => (float) ($data['additional_estimated_cost'] ?? 0.0),
            'currency' => $data['currency'] ?? $assignment->assignment_currency ?? 'USD',
            'status' => 'requested',
        ]);

        $this->auditService->record(
            tenantId: $assignment->tenant_id,
            eventType: 'assignment_extension_requested',
            action: 'create',
            entityType: 'MobilityExtension',
            entityId: $extension->id,
            actorId: $actor?->id ? (int) $actor->id : null,
            before: null,
            after: $extension->toArray()
        );

        return $extension;
    }

    /**
     * Approve an extension request and update the assignment.
     */
    public function approveExtension(MobilityExtension $extension, User $actor): MobilityExtension
    {
        $extension->update([
            'status' => 'approved',
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        $assignment = $extension->assignment;
        $before = $assignment->toArray();

        // Increment assignment version and set new planned end date
        $assignment->update([
            'planned_end_date' => $extension->proposed_end_date,
            'current_version' => $assignment->current_version + 1,
        ]);

        // Take snapshot for the new version
        $this->assignmentService->createVersionSnapshot(
            $assignment,
            "Extension approved: extended from {$extension->current_end_date} to {$extension->proposed_end_date}",
            $actor
        );

        $this->auditService->record(
            tenantId: $assignment->tenant_id,
            eventType: 'assignment_extension_approved',
            action: 'approve',
            entityType: 'MobilityExtension',
            entityId: $extension->id,
            actorId: (int) $actor->id,
            before: $before,
            after: $assignment->fresh()->toArray()
        );

        return $extension;
    }
}
