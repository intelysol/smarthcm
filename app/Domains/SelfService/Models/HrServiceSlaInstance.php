<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrServiceSlaInstance extends Model
{
    use HasUuids;

    protected $table = 'hr_service_sla_instances';

    protected $fillable = [
        'tenant_id',
        'hr_service_request_id',
        'hr_service_sla_policy_id',
        'response_due_at',
        'responded_at',
        'resolution_due_at',
        'resolved_at',
        'status',
        'total_paused_minutes',
        'last_paused_at',
    ];

    protected $casts = [
        'response_due_at' => 'datetime',
        'responded_at' => 'datetime',
        'resolution_due_at' => 'datetime',
        'resolved_at' => 'datetime',
        'last_paused_at' => 'datetime',
        'total_paused_minutes' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(HrServiceRequest::class, 'hr_service_request_id');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(HrServiceSlaPolicy::class, 'hr_service_sla_policy_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(HrServiceSlaEvent::class, 'hr_service_sla_instance_id');
    }
}
