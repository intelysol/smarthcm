<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAnalyticsDataQualityResult extends Model
{
    use HasUuids;

    protected $table = 'hcm_analytics_data_quality_results';

    protected $fillable = [
        'tenant_id',
        'check_id',
        'failed_records_count',
        'total_records_evaluated',
        'quality_score',
        'sample_failing_records',
        'evaluated_at',
    ];

    protected $casts = [
        'failed_records_count' => 'integer',
        'total_records_evaluated' => 'integer',
        'quality_score' => 'decimal:2',
        'sample_failing_records' => 'array',
        'evaluated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function check(): BelongsTo
    {
        return $this->belongsTo(HcmAnalyticsDataQualityCheck::class, 'check_id');
    }
}
