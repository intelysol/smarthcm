<?php

namespace App\Domains\WorkforcePlanning\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkforcePlanVersion extends Model
{
    use HasUuids;

    protected $table = 'hcm_workforce_plan_versions';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'version_number',
        'status',
        'change_rationale',
        'plan_payload',
        'created_by',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'plan_payload' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(HcmWorkforcePlan::class, 'plan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
