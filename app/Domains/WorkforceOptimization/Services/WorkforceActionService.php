<?php

namespace App\Domains\WorkforceOptimization\Services;

use App\Domains\WorkforceOptimization\Enums\RecommendationStatus;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationAction;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationAudit;
use App\Domains\WorkforceOptimization\Models\HcmWorkforceOptimizationRecommendation;
use App\Models\User;
use Illuminate\Support\Str;

class WorkforceActionService
{
    /**
     * Approve a recommendation and transition it to downstream execution plan.
     */
    public function approveRecommendation(
        HcmWorkforceOptimizationRecommendation $recommendation,
        ?User $approver = null,
        ?string $notes = null
    ): HcmWorkforceOptimizationAction {
        $recommendation->update([
            'lifecycle_status' => RecommendationStatus::APPROVED->value,
            'reviewed_by' => $approver?->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        // Audit Trail
        HcmWorkforceOptimizationAudit::create([
            'tenant_id' => $recommendation->tenant_id,
            'action' => 'APPROVED',
            'target_type' => 'RECOMMENDATION',
            'target_id' => $recommendation->id,
            'user_id' => $approver?->id,
            'changes' => [
                'old_status' => RecommendationStatus::GENERATED->value,
                'new_status' => RecommendationStatus::APPROVED->value,
                'notes' => $notes,
            ],
            'ip_address' => request()->ip() ?? '127.0.0.1',
        ]);

        // Create downstream execution action
        $targetSystem = match ($recommendation->action_type) {
            'HIRE_PERMANENT', 'CONTRACTOR_ENGAGE' => 'recruitment',
            'RESKILL' => 'learning',
            'SHIFT_REBALANCING', 'SCHEDULE_OPTIMIZE' => 'scheduling',
            'REDEPLOY_INTERNAL' => 'core_hr',
            default => 'core_hr',
        };

        $action = HcmWorkforceOptimizationAction::create([
            'tenant_id' => $recommendation->tenant_id,
            'recommendation_id' => $recommendation->id,
            'action_number' => 'ACT_' . strtoupper(substr(uniqid(), -8)),
            'title' => $recommendation->title,
            'target_module' => $targetSystem,
            'status' => 'pending',
            'payload' => $recommendation->toArray(),
            'created_by' => $approver?->id,
        ]);

        return $action;
    }

    /**
     * Reject a recommendation with reason code and narrative.
     */
    public function rejectRecommendation(
        HcmWorkforceOptimizationRecommendation $recommendation,
        User $rejector,
        string $reasonCode,
        ?string $narrative = null
    ): void {
        $recommendation->update([
            'lifecycle_status' => RecommendationStatus::REJECTED->value,
            'reviewed_by' => $rejector->id,
            'reviewed_at' => now(),
            'review_notes' => "Rejected ({$reasonCode}): {$narrative}",
        ]);

        // Audit Trail
        HcmWorkforceOptimizationAudit::create([
            'tenant_id' => $recommendation->tenant_id,
            'action' => 'REJECTED',
            'target_type' => 'RECOMMENDATION',
            'target_id' => $recommendation->id,
            'user_id' => $rejector->id,
            'changes' => [
                'old_status' => RecommendationStatus::GENERATED->value,
                'new_status' => RecommendationStatus::REJECTED->value,
                'reason_code' => $reasonCode,
                'narrative' => $narrative,
            ],
            'ip_address' => request()->ip() ?? '127.0.0.1',
        ]);
    }

    /**
     * Dispatch an action to downstream engine.
     */
    public function executeAction(HcmWorkforceOptimizationAction $action): HcmWorkforceOptimizationAction
    {
        $action->update([
            'status' => 'dispatched',
            'dispatched_at' => now(),
        ]);

        $action->recommendation->update([
            'lifecycle_status' => 'EXECUTING',
        ]);

        return $action;
    }
}
