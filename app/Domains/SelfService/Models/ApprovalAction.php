<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalAction extends PortalModel
{
    protected $fillable = ['approval_request_id', 'actor_employee_id', 'action', 'comments', 'attachments'];

    protected $casts = ['attachments' => 'array'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'actor_employee_id');
    }
}
