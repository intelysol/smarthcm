<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrServiceRequestAssignment extends Model
{
    use HasUuids;

    protected $table = 'hr_service_request_assignments';

    protected $fillable = [
        'tenant_id',
        'hr_service_request_id',
        'from_queue_id',
        'to_queue_id',
        'from_user_id',
        'to_user_id',
        'assigned_by_user_id',
        'reason',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(HrServiceRequest::class, 'hr_service_request_id');
    }

    public function fromQueue(): BelongsTo
    {
        return $this->belongsTo(HrServiceQueue::class, 'from_queue_id');
    }

    public function toQueue(): BelongsTo
    {
        return $this->belongsTo(HrServiceQueue::class, 'to_queue_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }
}
