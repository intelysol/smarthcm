<?php

namespace App\Domains\Lifecycle\Services;

use App\Domains\Lifecycle\Enums\PersonnelActionChangeType;
use App\Domains\Lifecycle\Enums\PersonnelActionStatus;
use App\Domains\Lifecycle\Models\PersonnelActionAudit;
use App\Domains\Lifecycle\Models\PersonnelActionChange;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Models\PersonnelActionReversal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PersonnelActionReversalService
{
    public function __construct(
        protected ?PersonnelActionService $actionService = null,
        protected ?PersonnelActionExecutionService $executionService = null
    ) {
        $this->actionService = $actionService ?? new PersonnelActionService();
        $this->executionService = $executionService ?? new PersonnelActionExecutionService();
    }

    public function reverseExecutedAction(PersonnelActionRequest $originalAction, User $user, string $reason): PersonnelActionReversal
    {
        if ($originalAction->status !== PersonnelActionStatus::EXECUTED->value) {
            throw ValidationException::withMessages(['status' => 'Only executed personnel actions can be reversed.']);
        }

        return DB::transaction(function () use ($originalAction, $user, $reason) {
            // 1. Create compensating action
            $reversalNumber = 'REV-' . $originalAction->request_number;

            $reversalRequest = PersonnelActionRequest::create([
                'tenant_id' => $originalAction->tenant_id,
                'employee_id' => $originalAction->employee_id,
                'action_type_id' => $originalAction->action_type_id,
                'request_number' => $reversalNumber,
                'status' => PersonnelActionStatus::APPROVED->value,
                'requested_by' => $user->id,
                'requested_at' => now(),
                'effective_date' => now()->toDateString(),
                'approved_at' => now(),
                'approved_by' => $user->id,
                'reason' => "Compensating reversal for {$originalAction->request_number}: {$reason}",
                'source' => 'reversal',
            ]);

            // 2. Invert each change (new_value becomes old_value, old_value becomes new_value)
            foreach ($originalAction->changes as $change) {
                PersonnelActionChange::create([
                    'tenant_id' => $originalAction->tenant_id,
                    'personnel_action_request_id' => $reversalRequest->id,
                    'field_name' => $change->field_name,
                    'entity_type' => $change->entity_type,
                    'entity_id' => $change->entity_id,
                    'old_value' => $change->new_value,
                    'new_value' => $change->old_value,
                    'old_value_label' => $change->new_value_label,
                    'new_value_label' => $change->old_value_label,
                    'change_type' => PersonnelActionChangeType::UPDATE->value,
                    'effective_date' => now()->toDateString(),
                ]);
            }

            // 3. Execute the compensating action to restore Core HR
            $this->executionService->execute($reversalRequest, $user);

            // 4. Mark original action as REVERSED
            $originalAction->update(['status' => PersonnelActionStatus::REVERSED->value]);

            // 5. Create permanent reversal audit record
            $reversalRecord = PersonnelActionReversal::create([
                'tenant_id' => $originalAction->tenant_id,
                'original_action_id' => $originalAction->id,
                'reversal_action_id' => $reversalRequest->id,
                'reason' => $reason,
                'requested_by' => $user->id,
                'approved_by' => $user->id,
                'executed_at' => now(),
            ]);

            PersonnelActionAudit::create([
                'tenant_id' => $originalAction->tenant_id,
                'personnel_action_request_id' => $originalAction->id,
                'actor_id' => $user->id,
                'action_event' => 'reversed',
                'reason' => $reason,
            ]);

            return $reversalRecord;
        });
    }
}
