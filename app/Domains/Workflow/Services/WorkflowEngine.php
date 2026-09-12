<?php

namespace App\Domains\Workflow\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Workflow\Models\Workflow;
use App\Domains\Workflow\Models\WorkflowAssignment;
use App\Domains\Workflow\Models\WorkflowInstance;
use App\Domains\Workflow\Models\WorkflowStep;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkflowEngine
{
    public function start(string $tenantId, string $trigger, Employee $requester, string $subjectType, string $subjectId, array $context = []): WorkflowInstance
    {
        return DB::transaction(function () use ($tenantId, $trigger, $requester, $subjectType, $subjectId, $context) {
            $workflow = Workflow::query()->where('tenant_id', $tenantId)->where('trigger', $trigger)->where('status', 'published')->with('steps')->latest('version')->firstOrFail();
            $instance = WorkflowInstance::query()->create(['tenant_id' => $tenantId, 'workflow_id' => $workflow->id, 'requester_employee_id' => $requester->id, 'subject_type' => $subjectType, 'subject_id' => $subjectId, 'context' => $context, 'status' => 'pending', 'submitted_at' => now()]);
            $this->event($instance, $requester, 'submitted', null, 'pending');
            $this->activateNextStep($instance, $requester);

            return $instance->refresh();
        });
    }

    public function act(WorkflowAssignment $assignment, Employee $actor, string $action, ?string $comments = null, ?array $attachments = null): WorkflowInstance
    {
        return DB::transaction(function () use ($assignment, $actor, $action, $comments, $attachments) {
            if ($assignment->assignee_employee_id !== $actor->id || $assignment->status !== 'pending') { throw ValidationException::withMessages(['assignment' => 'This approval is not available to you.']); }
            $instance = $assignment->instance()->lockForUpdate()->firstOrFail();
            if (! in_array($action, ['approved', 'rejected', 'returned'], true)) { throw ValidationException::withMessages(['action' => 'Unsupported workflow action.']); }
            $assignment->update(['status' => $action, 'acted_at' => now(), 'comments' => $comments, 'attachments' => $attachments]);
            $this->event($instance, $actor, $action, 'pending', $action, $comments, $attachments);
            if ($action !== 'approved') { $instance->update(['status' => $action, 'completed_at' => now()]); return $instance->refresh(); }
            $stepAssignments = $instance->assignments()->where('workflow_step_id', $assignment->workflow_step_id)->get();
            $strategy = $assignment->step->routing_strategy;
            $complete = $strategy === 'parallel_all' ? $stepAssignments->every(fn ($item) => $item->status === 'approved') : $stepAssignments->contains(fn ($item) => $item->status === 'approved');
            if ($complete) { $this->activateNextStep($instance, $actor); }

            return $instance->refresh();
        });
    }

    private function activateNextStep(WorkflowInstance $instance, Employee $actor): void
    {
        $currentOrder = $instance->currentStep?->step_order ?? 0;
        $step = WorkflowStep::query()->where('workflow_id', $instance->workflow_id)->where('step_order', '>', $currentOrder)->orderBy('step_order')->first();
        if ($step === null) { $instance->update(['status' => 'approved', 'completed_at' => now(), 'current_step_id' => null]); $this->event($instance, $actor, 'completed', 'pending', 'approved'); return; }
        $assignees = $step->approver_type === 'manager' ? collect([$instance->requester->reporting_manager_id])->filter() : collect([$step->approver_employee_id])->filter();
        if ($assignees->isEmpty()) { throw ValidationException::withMessages(['workflow' => "No approver can be resolved for step {$step->name}."]); }
        $instance->update(['current_step_id' => $step->id]);
        foreach ($assignees as $employeeId) { WorkflowAssignment::query()->create(['workflow_instance_id' => $instance->id, 'workflow_step_id' => $step->id, 'assignee_employee_id' => $employeeId, 'due_at' => $step->sla_hours ? now()->addHours($step->sla_hours) : null]); }
        $this->event($instance, $actor, 'routed', 'pending', 'pending', metadata: ['step_id' => $step->id]);
    }

    private function event(WorkflowInstance $instance, ?Employee $actor, string $event, ?string $old, ?string $new, ?string $comments = null, ?array $attachments = null, ?array $metadata = null): void
    {
        $instance->events()->create(['actor_employee_id' => $actor?->id, 'event_type' => $event, 'old_status' => $old, 'new_status' => $new, 'comments' => $comments, 'attachments' => $attachments, 'metadata' => $metadata, 'ip_address' => request()?->ip(), 'user_agent' => request()?->userAgent(), 'occurred_at' => now()]);
    }
}
