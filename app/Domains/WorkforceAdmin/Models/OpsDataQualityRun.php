<?php

namespace App\Domains\WorkforceAdmin\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpsDataQualityRun extends Model
{
    use HasUuids;

    protected $table = 'hcm_ops_data_quality_runs';

    protected $fillable = [
        'tenant_id',
        'run_number',
        'overall_score',
        'total_records_scanned',
        'total_violations_found',
        'scanned_at',
    ];

    protected $casts = [
        'overall_score' => 'decimal:2',
        'total_records_scanned' => 'integer',
        'total_violations_found' => 'integer',
        'scanned_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(OpsDataQualityResult::class, 'run_id');
    }
}
