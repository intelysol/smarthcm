<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsSlaPolicy extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_sla_policies';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'target_entity_type',
        'priority',
        'response_time_hours',
        'resolution_time_hours',
        'is_active',
    ];

    protected $casts = [
        'response_time_hours' => 'integer',
        'resolution_time_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
