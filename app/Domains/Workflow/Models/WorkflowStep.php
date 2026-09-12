<?php

namespace App\Domains\Workflow\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends WorkflowModel
{
    protected $fillable = ['workflow_id', 'step_order', 'name', 'routing_strategy', 'approver_type', 'approver_employee_id', 'role', 'conditions', 'sla_hours', 'reminder_hours'];
    protected $casts = ['conditions' => 'array'];
    public function workflow(): BelongsTo { return $this->belongsTo(Workflow::class); }
    public function approver(): BelongsTo { return $this->belongsTo(Employee::class, 'approver_employee_id'); }
}
