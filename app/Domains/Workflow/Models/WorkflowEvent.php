<?php

namespace App\Domains\Workflow\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowEvent extends WorkflowModel
{
    protected $fillable = ['workflow_instance_id', 'actor_employee_id', 'event_type', 'old_status', 'new_status', 'comments', 'attachments', 'metadata', 'ip_address', 'user_agent', 'occurred_at'];
    protected $casts = ['attachments' => 'array', 'metadata' => 'array', 'occurred_at' => 'datetime'];
    public function instance(): BelongsTo { return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id'); }
    public function actor(): BelongsTo { return $this->belongsTo(Employee::class, 'actor_employee_id'); }
}
