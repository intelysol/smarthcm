<?php

namespace App\Domains\SelfService\Services;

use App\Domains\SelfService\Enums\AssignmentMethod;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Events\ServiceRequestAssigned;
use App\Domains\SelfService\Events\ServiceRequestReassigned;
use App\Domains\SelfService\Models\HrServiceAssignmentRule;
use App\Domains\SelfService\Models\HrServiceQueue;
use App\Domains\SelfService\Models\HrServiceQueueMember;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceRequestAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RequestAssignmentService
{
    public function routeAndAssign(HrServiceRequest $request, ?User $assignedBy = null): HrServiceRequest
    {
        return DB::transaction(function () use ($request, $assignedBy) {
            $tenantId = $request->tenant_id;

            // 1. Evaluate Assignment Rules
            $rule = HrServiceAssignmentRule::query()
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->where(function ($q) use ($request) {
                    $q->where(fn ($sq) => $sq->where('hr_service_definition_id', $request->hr_service_definition_id))
                      ->orWhere(fn ($sq) => $sq->where('hr_service_category_id', $request->service?->hr_service_category_id))
                      ->orWhere(fn ($sq) => $sq->where('department_id', $request->department_id))
                      ->orWhere(fn ($sq) => $sq->where('branch_id', $request->branch_id))
                      ->orWhere(fn ($sq) => $sq->whereNull('hr_service_definition_id')
                                               ->whereNull('hr_service_category_id')
                                               ->whereNull('department_id')
                                               ->whereNull('branch_id'));
                })
                ->orderBy('priority', 'asc')
                ->first();

            $targetQueueId = $rule?->target_queue_id ?? $request->service?->default_queue_id;
            $targetUserId = $rule?->target_user_id;

            // If queue is found but no specific user, pick user based on queue strategy
            if ($targetQueueId && ! $targetUserId) {
                $queue = HrServiceQueue::find($targetQueueId);
                if ($queue) {
                    $targetUserId = $this->pickQueueAgent($queue);
                }
            }

            if ($targetQueueId || $targetUserId) {
                $this->assignTo($request, $targetQueueId, $targetUserId, $assignedBy, 'Automated rule & queue routing');
            }

            return $request->fresh(['assignedQueue', 'assignedUser']);
        });
    }

    public function assignTo(HrServiceRequest $request, ?string $toQueueId, ?int $toUserId, ?User $assignedBy = null, ?string $reason = null): HrServiceRequestAssignment
    {
        return DB::transaction(function () use ($request, $toQueueId, $toUserId, $assignedBy, $reason) {
            $fromQueueId = $request->assigned_queue_id;
            $fromUserId = $request->assigned_user_id;

            $assignment = HrServiceRequestAssignment::create([
                'tenant_id' => $request->tenant_id,
                'hr_service_request_id' => $request->id,
                'from_queue_id' => $fromQueueId,
                'to_queue_id' => $toQueueId,
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'assigned_by_user_id' => $assignedBy?->id,
                'reason' => $reason ?? 'Direct assignment',
                'assigned_at' => now(),
            ]);

            $request->update([
                'assigned_queue_id' => $toQueueId,
                'assigned_user_id' => $toUserId,
                'status' => in_array($request->status, [ServiceRequestStatus::SUBMITTED->value, ServiceRequestStatus::DRAFT->value])
                    ? ServiceRequestStatus::ASSIGNED->value
                    : $request->status,
            ]);

            // Adjust active tickets counter
            if ($toUserId && $toQueueId) {
                HrServiceQueueMember::where('tenant_id', $request->tenant_id)
                    ->where('hr_service_queue_id', $toQueueId)
                    ->where('user_id', $toUserId)
                    ->increment('active_tickets_count');
            }

            if ($fromUserId && $fromQueueId) {
                HrServiceQueueMember::where('tenant_id', $request->tenant_id)
                    ->where('hr_service_queue_id', $fromQueueId)
                    ->where('user_id', $fromUserId)
                    ->where('active_tickets_count', '>', 0)
                    ->decrement('active_tickets_count');
            }

            event(new ServiceRequestAssigned($request));

            return $assignment;
        });
    }

    public function pickQueueAgent(HrServiceQueue $queue): ?int
    {
        $membersQuery = $queue->members()->where('is_available', true);

        if ($queue->assignment_method === AssignmentMethod::WORKLOAD_BASED->value) {
            $candidate = $membersQuery->orderBy('active_tickets_count', 'asc')->first();
            return $candidate?->user_id;
        }

        // Round Robin / Default
        $candidate = $membersQuery->orderBy('updated_at', 'asc')->first();
        if ($candidate) {
            $candidate->touch(); // Update timestamp to move to end of queue
            return $candidate->user_id;
        }

        return null;
    }
}
