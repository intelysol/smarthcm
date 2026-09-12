<?php

namespace App\Domains\Workflow\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowInstance extends WorkflowModel
{
    protected $fillable = ['tenant_id', 'workflow_id', 'requester_employee_id', 'subject_type', 'subject_id', 'context', 'status', 'current_step_id', 'submitted_at', 'completed_at'];
    protected $casts = ['context' => 'array', 'submitted_at' => 'datetime', 'completed_at' => 'datetime'];
    public function workflow(): BelongsTo { return $this->belongsTo(Workflow::class); }
    public function requester(): BelongsTo { return $this->belongsTo(Employee::class, 'requester_employee_id'); }
    public function currentStep(): BelongsTo { return $this->belongsTo(WorkflowStep::class, 'current_step_id'); }
    public function assignments(): HasMany { return $this->hasMany(WorkflowAssignment::class); }
    public function events(): HasMany { return $this->hasMany(WorkflowEvent::class)->orderBy('occurred_at'); }
}
