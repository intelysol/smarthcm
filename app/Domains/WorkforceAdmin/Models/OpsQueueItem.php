<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsQueueItem extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_queue_items';

    protected $fillable = [
        'tenant_id',
        'queue_id',
        'item_number',
        'title',
        'entity_type',
        'entity_id',
        'employee_id',
        'priority',
        'status',
        'assigned_to',
        'assigned_team',
        'due_at',
        'first_responded_at',
        'resolved_at',
        'is_sla_breached',
        'payload',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'first_responded_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_sla_breached' => 'boolean',
        'payload' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(OpsQueue::class, 'queue_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
