<?php

namespace App\Domains\Mobility\Services;

use App\Domains\Mobility\Enums\AssignmentStatus;
use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityAssignmentLocation;
use App\Domains\Mobility\Models\MobilityAssignmentTerm;
use App\Domains\Mobility\Models\MobilityAssignmentVersion;
use App\Domains\Mobility\Models\MobilityRequest;
use App\Models\User;
use App\Domains\Shared\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MobilityAssignmentService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Create an assignment from an approved Mobility Request.
     */
    public function createFromRequest(MobilityRequest $request, ?User $actor = null): MobilityAssignment
    {
        $assignmentNumber = 'ASN-' . strtoupper(Str::random(8));

        $startDate = $request->proposed_start_date ? Carbon::parse($request->proposed_start_date) : now();
        $plannedEndDate = $request->proposed_end_date ? Carbon::parse($request->proposed_end_date) : $startDate->copy()->addMonths($request->duration_months ?? 12);

        $assignment = MobilityAssignment::create([
            'tenant_id' => $request->tenant_id,
            'mobility_request_id' => $request->id,
            'employee_id' => $request->employee_id,
            'program_id' => $request->program_id,
            'assignment_number' => $assignmentNumber,
            'mobility_type' => $request->mobility_type->value ?? 'international_assignment',
            'status' => AssignmentStatus::PLANNING->value,
            'current_version' => 1,
            'home_country' => $request->home_country,
            'host_country' => $request->host_country,
            'home_company_id' => $request->home_company_id,
            'home_branch_id' => $request->home_branch_id,
            'home_department_id' => $request->home_department_id,
            'home_position_id' => $request->home_position_id,
            'home_job_id' => $request->home_job_id,
            'home_manager_id' => $request->home_manager_id,
            'host_company_id' => $request->host_company_id,
            'host_branch_id' => $request->host_branch_id,
            'host_department_id' => $request->host_department_id,
            'host_position_id' => $request->host_position_id,
            'host_job_id' => $request->host_job_id,
            'host_manager_id' => $request->host_manager_id,
            'start_date' => $startDate->toDateString(),
            'planned_end_date' => $plannedEndDate->toDateString(),
            'assignment_sponsor_id' => $request->assignment_sponsor_id,
            'mobility_owner_id' => $request->mobility_owner_id,
            'cost_center_code' => $request->cost_center_code,
            'purpose' => $request->assignment_reason ?? 'International Assignment',
        ]);

        // Create initial Terms record
        MobilityAssignmentTerm::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'home_employment_terms' => 'continuous_employment',
            'host_employment_terms' => 'seconded',
            'tax_treatment' => 'tax_equalization',
            'repatriation_terms' => 'return_to_equivalent',
        ]);

        // Create Home and Host Locations
        MobilityAssignmentLocation::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'location_type' => 'home',
            'country' => $assignment->home_country,
        ]);

        MobilityAssignmentLocation::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'location_type' => 'host',
            'country' => $assignment->host_country,
        ]);

        // Snapshot Version 1
        $this->createVersionSnapshot($assignment, 'Initial assignment creation', $actor);

        $this->auditService->record(
            tenantId: $assignment->tenant_id,
            eventType: 'mobility_assignment_created',
            action: 'create',
            entityType: 'MobilityAssignment',
            entityId: $assignment->id,
            actorId: $actor?->id ? (int) $actor->id : null,
            before: null,
            after: $assignment->toArray()
        );

        return $assignment;
    }

    /**
     * Activate the assignment.
     */
    public function activateAssignment(MobilityAssignment $assignment, ?User $actor = null): MobilityAssignment
    {
        $before = $assignment->toArray();

        $assignment->update([
            'status' => AssignmentStatus::ACTIVE->value,
        ]);

        $this->createVersionSnapshot($assignment, 'Assignment activated', $actor);

        $this->auditService->record(
            tenantId: $assignment->tenant_id,
            eventType: 'mobility_assignment_activated',
            action: 'activate',
            entityType: 'MobilityAssignment',
            entityId: $assignment->id,
            actorId: $actor?->id ? (int) $actor->id : null,
            before: $before,
            after: $assignment->fresh()->toArray()
        );

        return $assignment;
    }

    /**
     * Complete the assignment.
     */
    public function completeAssignment(MobilityAssignment $assignment, ?string $actualEndDate = null, ?User $actor = null): MobilityAssignment
    {
        $before = $assignment->toArray();

        $assignment->update([
            'status' => AssignmentStatus::COMPLETED->value,
            'actual_end_date' => $actualEndDate ?? now()->toDateString(),
        ]);

        $this->createVersionSnapshot($assignment, 'Assignment completed', $actor);

        $this->auditService->record(
            tenantId: $assignment->tenant_id,
            eventType: 'mobility_assignment_completed',
            action: 'complete',
            entityType: 'MobilityAssignment',
            entityId: $assignment->id,
            actorId: $actor?->id ? (int) $actor->id : null,
            before: $before,
            after: $assignment->fresh()->toArray()
        );

        return $assignment;
    }

    /**
     * Creates an immutable version snapshot of the assignment.
     */
    public function createVersionSnapshot(MobilityAssignment $assignment, string $reason, ?User $actor = null): MobilityAssignmentVersion
    {
        $currentVersion = $assignment->current_version;

        // Eager-load relations for full state representation
        $assignment->loadMissing(['terms', 'locations', 'costs', 'costAllocations']);

        return MobilityAssignmentVersion::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'version_number' => $currentVersion,
            'version_reason' => $reason,
            'snapshot_payload' => $assignment->toArray(),
            'created_by' => $actor?->id,
        ]);
    }
}
