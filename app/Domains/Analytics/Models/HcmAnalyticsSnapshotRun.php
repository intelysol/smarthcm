<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAnalyticsSnapshotRun extends Model
{
    use HasUuids;

    protected $table = 'hcm_analytics_snapshot_runs';

    protected $fillable = [
        'tenant_id',
        'snapshot_type',
        'snapshot_date',
        'status',
        'records_processed',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'records_processed' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
