<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpsQueue extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_ops_queues';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'category',
        'description',
        'default_priority',
        'target_sla_hours',
        'is_active',
    ];

    protected $casts = [
        'target_sla_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OpsQueueItem::class, 'queue_id');
    }
}
