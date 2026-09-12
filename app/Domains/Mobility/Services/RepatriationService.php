<?php

namespace App\Domains\Mobility\Services;

use App\Domains\Mobility\Enums\AssignmentStatus;
use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityRepatriation;
use App\Models\User;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Str;

class RepatriationService
{
    public function __construct(
        protected MobilityAssignmentService $assignmentService,
        protected AuditService $auditService
    ) {}

    /**
     * Initiate repatriation planning for an assignment nearing completion.
     */
    public function initiateRepatriation(MobilityAssignment $assignment, array $data, ?User $actor = null): MobilityRepatriation
    {
        $repatriationNumber = 'REP-' . strtoupper(Str::random(8));

        $repatriation = MobilityRepatriation::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'repatriation_number' => $repatriationNumber,
            'planned_return_date' => $data['planned_return_date'] ?? $assignment->planned_end_date,
            'outcome_type' => $data['outcome_type'] ?? 'return_to_original',
            'return_company_id' => $data['return_company_id'] ?? $assignment->home_company_id,
            'return_department_id' => $data['return_department_id'] ?? $assignment->home_department_id,
            'return_position_id' => $data['return_position_id'] ?? $assignment->home_position_id,
            'return_manager_id' => $data['return_manager_id'] ?? $assignment->home_manager_id,
            'status' => 'planning',
            'notes' => $data['notes'] ?? null,
        ]);

        $assignment->update([
            'status' => AssignmentStatus::REPATRIATING->value,
        ]);

        $this->auditService->record(
            tenantId: $assignment->tenant_id,
            eventType: 'repatriation_initiated',
            action: 'create',
            entityType: 'MobilityRepatriation',
            entityId: $repatriation->id,
            actorId: $actor?->id ? (int) $actor->id : null,
            before: null,
            after: $repatriation->toArray()
        );

        return $repatriation;
    }

    /**
     * Complete repatriation checklist and close the assignment.
     */
    public function completeRepatriation(MobilityRepatriation $repatriation, ?User $actor = null): MobilityRepatriation
    {
        $repatriation->update([
            'status' => 'completed',
            'actual_return_date' => now()->toDateString(),
            'expense_settlement_completed' => true,
            'advance_settlement_completed' => true,
            'compliance_closure_completed' => true,
            'payroll_transition_completed' => true,
        ]);

        $assignment = $repatriation->assignment;
        $assignment->update([
            'status' => AssignmentStatus::COMPLETED->value,
            'is_repatriated' => true,
            'repatriation_date' => now()->toDateString(),
            'actual_end_date' => now()->toDateString(),
        ]);

        $this->assignmentService->createVersionSnapshot($assignment, 'Repatriation completed', $actor);

        $this->auditService->record(
            tenantId: $repatriation->tenant_id,
            eventType: 'repatriation_completed',
            action: 'complete',
            entityType: 'MobilityRepatriation',
            entityId: $repatriation->id,
            actorId: $actor?->id ? (int) $actor->id : null,
            before: null,
            after: $repatriation->fresh()->toArray()
        );

        return $repatriation;
    }
}
