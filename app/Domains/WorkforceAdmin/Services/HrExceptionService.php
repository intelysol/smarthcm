<?php

namespace App\Domains\WorkforceAdmin\Services;

use App\Domains\Shared\Services\AuditService;
use App\Domains\WorkforceAdmin\Enums\ExceptionStatus;
use App\Domains\WorkforceAdmin\Models\OpsException;
use App\Domains\WorkforceAdmin\Models\OpsExceptionAssignment;
use App\Domains\WorkforceAdmin\Models\OpsExceptionEvent;
use App\Models\User;
use Illuminate\Support\Str;

class HrExceptionService
{
    public function __construct(
        protected SlaMonitoringService $slaService,
        protected AuditService $auditService
    ) {}

    /**
     * Record a detected operational exception.
     */
    public function recordException(array $data): OpsException
    {
        $exceptionNumber = 'EXC-' . strtoupper(Str::random(8));

        $exception = OpsException::create([
            'tenant_id' => $data['tenant_id'],
            'exception_number' => $exceptionNumber,
            'exception_type' => $data['exception_type'],
            'severity' => $data['severity'] ?? 'medium',
            'domain' => $data['domain'],
            'entity_type' => $data['entity_type'],
            'entity_id' => $data['entity_id'],
            'employee_id' => $data['employee_id'] ?? null,
            'description' => $data['description'],
            'status' => ExceptionStatus::DETECTED->value,
            'owner_id' => $data['owner_id'] ?? null,
            'assigned_team' => $data['assigned_team'] ?? null,
            'resolution_guidance' => $data['resolution_guidance'] ?? null,
        ]);

        OpsExceptionEvent::create([
            'tenant_id' => $exception->tenant_id,
            'exception_id' => $exception->id,
            'event_type' => 'status_change',
            'from_status' => null,
            'to_status' => ExceptionStatus::DETECTED->value,
            'comment' => 'Exception detected automatically by monitoring engine.',
        ]);

        // Start SLA tracking
        $this->slaService->startSlaTracking('exception', $exception->id, $exception->tenant_id, $exception->severity);

        return $exception;
    }

    /**
     * Assign exception to user/team and transition status.
     */
    public function assignException(OpsException $exception, ?User $assignee = null, ?string $team = null, ?User $assignedBy = null): OpsException
    {
        $fromStatus = $exception->status->value;

        $exception->update([
            'owner_id' => $assignee?->id,
            'assigned_team' => $team,
            'status' => ExceptionStatus::ASSIGNED->value,
        ]);

        OpsExceptionAssignment::create([
            'tenant_id' => $exception->tenant_id,
            'exception_id' => $exception->id,
            'assigned_user_id' => $assignee?->id,
            'assigned_team' => $team,
            'assigned_by' => $assignedBy?->id,
            'notes' => 'Assigned for investigation.',
        ]);

        OpsExceptionEvent::create([
            'tenant_id' => $exception->tenant_id,
            'exception_id' => $exception->id,
            'event_type' => 'status_change',
            'from_status' => $fromStatus,
            'to_status' => ExceptionStatus::ASSIGNED->value,
            'actor_id' => $assignedBy?->id,
        ]);

        $this->slaService->recordFirstResponse('exception', $exception->id);

        return $exception;
    }

    /**
     * Resolve exception.
     */
    public function resolveException(OpsException $exception, string $resolutionNotes, User $actor): OpsException
    {
        $fromStatus = $exception->status->value;

        $exception->update([
            'status' => ExceptionStatus::RESOLVED->value,
            'resolution_notes' => $resolutionNotes,
            'resolved_at' => now(),
            'resolved_by' => $actor->id,
        ]);

        OpsExceptionEvent::create([
            'tenant_id' => $exception->tenant_id,
            'exception_id' => $exception->id,
            'event_type' => 'status_change',
            'from_status' => $fromStatus,
            'to_status' => ExceptionStatus::RESOLVED->value,
            'comment' => $resolutionNotes,
            'actor_id' => $actor->id,
        ]);

        $this->slaService->recordResolution('exception', $exception->id);

        return $exception;
    }
}
