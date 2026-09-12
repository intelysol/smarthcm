<?php

namespace App\Domains\WorkforceProductivity\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmProductivityBenchmark extends Model
{
    use HasUuids;

    protected $table = 'hcm_productivity_benchmarks';

    protected $fillable = [
        'tenant_id',
        'benchmark_name',
        'benchmark_type',
        'baseline_period_start',
        'baseline_period_end',
        'baseline_productivity_rate',
        'comparison_period_start',
        'comparison_period_end',
        'comparison_productivity_rate',
        'normalized_index',
        'variance_pct',
        'benchmark_data',
    ];

    protected $casts = [
        'baseline_period_start' => 'date',
        'baseline_period_end' => 'date',
        'baseline_productivity_rate' => 'decimal:4',
        'comparison_period_start' => 'date',
        'comparison_period_end' => 'date',
        'comparison_productivity_rate' => 'decimal:4',
        'normalized_index' => 'decimal:2',
        'variance_pct' => 'decimal:2',
        'benchmark_data' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
