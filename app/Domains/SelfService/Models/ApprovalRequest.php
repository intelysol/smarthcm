<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalRequest extends PortalModel
{
    protected $fillable = ['tenant_id', 'requester_employee_id', 'current_approver_employee_id', 'module', 'subject_type', 'subject_id', 'status', 'title', 'comments', 'attachments', 'due_at'];

    protected $casts = ['attachments' => 'array', 'due_at' => 'datetime'];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requester_employee_id');
    }

    public function currentApprover(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'current_approver_employee_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class);
    }
}
