<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Services;

use App\Domains\HealthSafety\Models\HcmSafetyIncident;
use App\Domains\HealthSafety\Models\HcmSafetyIncidentAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CorrectiveActionService
{
    /**
     * Create a corrective or preventive action (CAPA) linked to an incident.
     */
    public function createAction(array $data, ?int $userId = null): HcmSafetyIncidentAction
    {
        $incident = HcmSafetyIncident::findOrFail($data['incident_id']);

        return HcmSafetyIncidentAction::create([
            'tenant_id' => $incident->tenant_id,
            'safety_incident_id' => $incident->id,
            'action_type' => $data['action_type'] ?? HcmSafetyIncidentAction::TYPE_CORRECTIVE,
            'title' => $data['title'],
            'description' => $data['description'],
            'assigned_to' => $userId,
            'due_date' => $data['due_date'],
            'status' => HcmSafetyIncidentAction::STATUS_OPEN,
            'priority' => $data['priority'] ?? 'medium',
        ]);
    }

    /**
     * Mark action as completed by assignee.
     */
    public function completeAction(
        HcmSafetyIncidentAction $action,
        string $completionNotes,
        int $userId
    ): HcmSafetyIncidentAction {
        if ($action->status === HcmSafetyIncidentAction::STATUS_VERIFIED) {
            throw ValidationException::withMessages([
                'status' => ['Action is already verified and closed.'],
            ]);
        }

        $action->update([
            'status' => HcmSafetyIncidentAction::STATUS_COMPLETED,
            'completed_date' => Carbon::now()->toDateString(),
            'description' => $action->description . "\n[Completion notes: {$completionNotes}]",
        ]);

        return $action->fresh();
    }

    /**
     * Safety officer verifies effectiveness of completed action.
     */
    public function verifyAction(
        HcmSafetyIncidentAction $action,
        bool $isEffective,
        string $verificationNotes,
        int $verifierId
    ): HcmSafetyIncidentAction {
        if ($action->status !== HcmSafetyIncidentAction::STATUS_COMPLETED) {
            throw ValidationException::withMessages([
                'status' => ['Only completed actions can be verified for effectiveness.'],
            ]);
        }

        if ($isEffective) {
            $action->update([
                'status' => HcmSafetyIncidentAction::STATUS_VERIFIED,
                'verified_at' => Carbon::now(),
                'verified_by' => $verifierId,
                'description' => $action->description . "\n[Verified: {$verificationNotes}]",
            ]);
        } else {
            // Re-open if not effective
            $action->update([
                'status' => HcmSafetyIncidentAction::STATUS_OPEN,
                'description' => $action->description . "\n[FAILED EFFECTIVENESS: {$verificationNotes}]",
                'completed_date' => null,
            ]);
        }

        return $action->fresh();
    }


    /**
     * Get all overdue open actions across tenant.
     */
    public function getOverdueActions(string $tenantId): Collection
    {
        return HcmSafetyIncidentAction::where('tenant_id', $tenantId)
            ->whereIn('status', [HcmSafetyIncidentAction::STATUS_OPEN, HcmSafetyIncidentAction::STATUS_IN_PROGRESS])
            ->whereDate('due_date', '<', Carbon::today())
            ->with(['incident', 'assignee'])
            ->get();
    }
}
