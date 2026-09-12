<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsSlaInstance extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_sla_instances';

    protected $fillable = [
        'tenant_id',
        'sla_policy_id',
        'target_entity_type',
        'target_entity_id',
        'started_at',
        'response_due_at',
        'first_response_at',
        'resolution_due_at',
        'resolved_at',
        'status',
        'is_breached',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'response_due_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolution_due_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_breached' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(OpsSlaPolicy::class, 'sla_policy_id');
    }
}
