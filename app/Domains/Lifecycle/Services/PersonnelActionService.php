<?php

namespace App\Domains\Lifecycle\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Lifecycle\Enums\PersonnelActionChangeType;
use App\Domains\Lifecycle\Enums\PersonnelActionStatus;
use App\Domains\Lifecycle\Models\PersonnelActionAudit;
use App\Domains\Lifecycle\Models\PersonnelActionChange;
use App\Domains\Lifecycle\Models\PersonnelActionRequest;
use App\Domains\Lifecycle\Models\PersonnelActionType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PersonnelActionService
{
    public function __construct(
        protected ?PersonnelActionValidationService $validationService = null,
        protected ?PersonnelActionImpactService $impactService = null,
        protected ?PersonnelActionExecutionService $executionService = null
    ) {
        $this->validationService = $validationService ?? new PersonnelActionValidationService();
        $this->impactService = $impactService ?? new PersonnelActionImpactService();
        $this->executionService = $executionService ?? new PersonnelActionExecutionService();
    }

    public function generateRequestNumber(string $tenantId): string
    {
        $year = date('Y');
        $count = PersonnelActionRequest::where('tenant_id', $tenantId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('PA-%s-%06d', $year, $count);
    }

    public function createRequest(User $user, array $data): PersonnelActionRequest
    {
        $employee = Employee::findOrFail($data['employee_id']);
        $actionType = PersonnelActionType::findOrFail($data['action_type_id']);
        $effectiveDate = Carbon::parse($data['effective_date']);

        // Validate baseline constraints
        $this->validationService->validateRequestCreation($user, $employee, $actionType, $effectiveDate, $data['changes'] ?? []);

        return DB::transaction(function () use ($user, $employee, $actionType, $effectiveDate, $data) {
            $requestNumber = $this->generateRequestNumber($user->tenant_id);

            $request = PersonnelActionRequest::create([
                'tenant_id' => $user->tenant_id,
                'employee_id' => $employee->id,
                'action_type_id' => $actionType->id,
                'request_number' => $requestNumber,
                'status' => PersonnelActionStatus::DRAFT->value,
                'requested_by' => $user->id,
                'requested_at' => now(),
                'effective_date' => $effectiveDate->toDateString(),
                'reason' => $data['reason'] ?? null,
                'comments' => $data['comments'] ?? null,
                'source' => $data['source'] ?? 'manual',
                'priority' => $data['priority'] ?? 'medium',
            ]);

            // Save Normalized Changes (Current vs Proposed State)
            if (!empty($data['changes'])) {
                foreach ($data['changes'] as $change) {
                    PersonnelActionChange::create([
                        'tenant_id' => $user->tenant_id,
                        'personnel_action_request_id' => $request->id,
                        'field_name' => $change['field_name'],
                        'entity_type' => $change['entity_type'] ?? 'employee',
                        'entity_id' => $change['entity_id'] ?? $employee->id,
                        'old_value' => $change['old_value'] ?? null,
                        'new_value' => $change['new_value'] ?? null,
                        'old_value_label' => $change['old_value_label'] ?? null,
                        'new_value_label' => $change['new_value_label'] ?? null,
                        'change_type' => $change['change_type'] ?? PersonnelActionChangeType::UPDATE->value,
                        'effective_date' => $effectiveDate->toDateString(),
                    ]);
                }
            }

            // Run initial impact analysis
            $this->impactService->analyzeImpact($request);

            // Audit log
            PersonnelActionAudit::create([
                'tenant_id' => $user->tenant_id,
                'personnel_action_request_id' => $request->id,
                'actor_id' => $user->id,
                'action_event' => 'created',
                'new_values' => ['request_number' => $requestNumber, 'effective_date' => $effectiveDate->toDateString()],
                'reason' => $data['reason'] ?? 'Personnel action request initiated',
            ]);

            return $request;
        });
    }

    public function submitRequest(PersonnelActionRequest $request, User $user): PersonnelActionRequest
    {
        if ($request->status !== PersonnelActionStatus::DRAFT->value) {
            throw ValidationException::withMessages(['status' => 'Only draft personnel actions can be submitted.']);
        }

        // Run full validation including conflict detection
        $this->validationService->validateSubmission($request);

        $request->update([
            'status' => PersonnelActionStatus::PENDING_APPROVAL->value,
            'submitted_at' => now(),
        ]);

        PersonnelActionAudit::create([
            'tenant_id' => $request->tenant_id,
            'personnel_action_request_id' => $request->id,
            'actor_id' => $user->id,
            'action_event' => 'submitted',
            'reason' => 'Submitted for managerial/HR approval',
        ]);

        return $request;
    }

    public function approveRequest(PersonnelActionRequest $request, User $approver, ?string $comments = null): PersonnelActionRequest
    {
        if (!in_array($request->status, [PersonnelActionStatus::SUBMITTED->value, PersonnelActionStatus::PENDING_APPROVAL->value, PersonnelActionStatus::UNDER_REVIEW->value])) {
            throw ValidationException::withMessages(['status' => 'Personnel action is not awaiting approval.']);
        }

        return DB::transaction(function () use ($request, $approver, $comments) {
            $isDue = Carbon::parse($request->effective_date)->lessThanOrEqualTo(now()->toDateString());
            $newStatus = $isDue ? PersonnelActionStatus::APPROVED->value : PersonnelActionStatus::SCHEDULED->value;

            $request->update([
                'status' => $newStatus,
                'approved_at' => now(),
                'approved_by' => $approver->id,
                'comments' => $comments ?? $request->comments,
            ]);

            PersonnelActionAudit::create([
                'tenant_id' => $request->tenant_id,
                'personnel_action_request_id' => $request->id,
                'actor_id' => $approver->id,
                'action_event' => 'approved',
                'reason' => $comments ?? 'Approved by authorized manager/HR',
            ]);

            // If effective date has arrived or passed, execute immediately
            if ($isDue) {
                $this->executionService->execute($request, $approver);
            }

            return $request;
        });
    }

    public function rejectRequest(PersonnelActionRequest $request, User $rejecter, string $reason): PersonnelActionRequest
    {
        $request->update([
            'status' => PersonnelActionStatus::REJECTED->value,
            'comments' => $reason,
        ]);

        PersonnelActionAudit::create([
            'tenant_id' => $request->tenant_id,
            'personnel_action_request_id' => $request->id,
            'actor_id' => $rejecter->id,
            'action_event' => 'rejected',
            'reason' => $reason,
        ]);

        return $request;
    }

    public function cancelRequest(PersonnelActionRequest $request, User $user, string $reason): PersonnelActionRequest
    {
        if (in_array($request->status, [PersonnelActionStatus::EXECUTED->value, PersonnelActionStatus::REVERSED->value])) {
            throw ValidationException::withMessages(['status' => 'Executed personnel actions cannot be cancelled; use formal reversal instead.']);
        }

        $request->update([
            'status' => PersonnelActionStatus::CANCELLED->value,
            'comments' => $reason,
        ]);

        PersonnelActionAudit::create([
            'tenant_id' => $request->tenant_id,
            'personnel_action_request_id' => $request->id,
            'actor_id' => $user->id,
            'action_event' => 'cancelled',
            'reason' => $reason,
        ]);

        return $request;
    }
}
