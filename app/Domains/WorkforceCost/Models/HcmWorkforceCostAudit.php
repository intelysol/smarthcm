<?php

namespace App\Domains\WorkforceCost\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforceCostAudit extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_cost_audits';

    protected $fillable = [
        'tenant_id',
        'action_type',
        'entity_type',
        'entity_id',
        'performed_by',
        'previous_state',
        'new_state',
        'ip_address',
    ];

    protected $casts = [
        'previous_state' => 'array',
        'new_state' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}