<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityIntegrationRecord extends Model
{
    use HasUuids;

    protected $table = 'hcm_mobility_integration_records';

    protected $fillable = [
        'tenant_id',
        'integration_target',
        'entity_type',
        'entity_id',
        'status',
        'payload',
        'external_reference',
        'attempt_count',
        'dispatched_at',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempt_count' => 'integer',
        'dispatched_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
