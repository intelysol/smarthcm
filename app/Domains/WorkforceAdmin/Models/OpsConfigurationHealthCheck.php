<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsConfigurationHealthCheck extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_configuration_health_checks';

    protected $fillable = [
        'tenant_id',
        'config_category',
        'check_name',
        'health_status',
        'diagnostic_message',
        'last_checked_at',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
