<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsDeadline extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_deadlines';

    protected $fillable = [
        'tenant_id',
        'name',
        'domain',
        'cutoff_date',
        'lead_days_warning',
        'status',
        'instructions',
    ];

    protected $casts = [
        'cutoff_date' => 'date',
        'lead_days_warning' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
