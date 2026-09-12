<?php

namespace App\Domains\Workflow\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowAssignment extends WorkflowModel
{
    protected $fillable = ['workflow_instance_id', 'workflow_step_id', 'assignee_employee_id', 'status', 'due_at', 'acted_at', 'comments', 'attachments'];
    protected $casts = ['due_at' => 'datetime', 'acted_at' => 'datetime', 'attachments' => 'array'];
    public function instance(): BelongsTo { return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id'); }
    public function step(): BelongsTo { return $this->belongsTo(WorkflowStep::class, 'workflow_step_id'); }
    public function assignee(): BelongsTo { return $this->belongsTo(Employee::class, 'assignee_employee_id'); }
}
