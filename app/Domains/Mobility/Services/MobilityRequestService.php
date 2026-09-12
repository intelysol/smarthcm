<?php

namespace App\Domains\Mobility\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Mobility\Enums\MobilityRequestStatus;
use App\Domains\Mobility\Models\MobilityProgram;
use App\Domains\Mobility\Models\MobilityRequest;
use App\Models\User;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Str;

class MobilityRequestService
{
    public function __construct(
        protected MobilityEligibilityService $eligibilityService,
        protected AuditService $auditService
    ) {}

    /**
     * Create a new Mobility Request.
     */
    public function createRequest(array $data, ?User $actor = null): MobilityRequest
    {
        $employee = Employee::findOrFail($data['employee_id']);
        $program = !empty($data['program_id']) ? MobilityProgram::find($data['program_id']) : null;

        // Auto evaluate eligibility
        $eligibilityResult = $this->eligibilityService->evaluateEligibility($employee, $program, $data);

        $requestNumber = 'MOB-REQ-' . strtoupper(Str::random(8));

        $payload = array_merge($data, [
            'request_number' => $requestNumber,
            'status' => MobilityRequestStatus::DRAFT->value,
            'eligibility_status' => $eligibilityResult['status']->value,
            'eligibility_details' => $eligibilityResult,
        ]);

        $request = MobilityRequest::create($payload);

        $this->auditService->record(
            tenantId: $request->tenant_id,
            eventType: 'mobility_request_created',
            action: 'create',
            entityType: 'MobilityRequest',
            entityId: $request->id,
            actorId: $actor?->id ? (int) $actor->id : null,
            before: null,
            after: $request->toArray()
        );

        return $request;
    }

    /**
     * Submit request for approvals.
     */
    public function submitRequest(MobilityRequest $request, ?User $actor = null): MobilityRequest
    {
        $before = $request->toArray();

        $request->update([
            'status' => MobilityRequestStatus::SUBMITTED->value,
        ]);

        $this->auditService->record(
            tenantId: $request->tenant_id,
            eventType: 'mobility_request_submitted',
            action: 'submit',
            entityType: 'MobilityRequest',
            entityId: $request->id,
            actorId: $actor?->id ? (int) $actor->id : null,
            before: $before,
            after: $request->fresh()->toArray()
        );

        return $request;
    }

    /**
     * Approve mobility request.
     */
    public function approveRequest(MobilityRequest $request, User $actor): MobilityRequest
    {
        $before = $request->toArray();

        $request->update([
            'status' => MobilityRequestStatus::APPROVED->value,
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        $this->auditService->record(
            tenantId: $request->tenant_id,
            eventType: 'mobility_request_approved',
            action: 'approve',
            entityType: 'MobilityRequest',
            entityId: $request->id,
            actorId: (int) $actor->id,
            before: $before,
            after: $request->fresh()->toArray()
        );

        return $request;
    }

    /**
     * Reject mobility request.
     */
    public function rejectRequest(MobilityRequest $request, string $reason, User $actor): MobilityRequest
    {
        $before = $request->toArray();

        $request->update([
            'status' => MobilityRequestStatus::REJECTED->value,
            'rejection_reason' => $reason,
        ]);

        $this->auditService->record(
            tenantId: $request->tenant_id,
            eventType: 'mobility_request_rejected',
            action: 'reject',
            entityType: 'MobilityRequest',
            entityId: $request->id,
            actorId: (int) $actor->id,
            before: $before,
            after: $request->fresh()->toArray()
        );

        return $request;
    }
}
