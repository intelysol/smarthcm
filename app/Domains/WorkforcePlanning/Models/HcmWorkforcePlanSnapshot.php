<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforcePlanSnapshot extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_plan_snapshots';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'version_number',
        'full_plan_state',
        'frozen_at',
        'frozen_by',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'full_plan_state' => 'array',
        'frozen_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePlan::class, 'plan_id');
    }

    public function frozenByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'frozen_by');
    }
}
