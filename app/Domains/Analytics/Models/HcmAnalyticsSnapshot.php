<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmAnalyticsSnapshot extends Model
{
    use HasUuids;

    protected $table = 'hcm_analytics_snapshots';

    protected $fillable = [
        'tenant_id',
        'snapshot_type',
        'snapshot_date',
        'headcount_total',
        'headcount_active',
        'headcount_inactive',
        'fte_total',
        'full_time_count',
        'part_time_count',
        'contractor_count',
        'new_hires_count',
        'transfers_in_count',
        'transfers_out_count',
        'promotions_count',
        'terminations_count',
        'voluntary_exits_count',
        'involuntary_exits_count',
        'by_department',
        'by_branch',
        'by_job_grade',
        'by_employment_type',
        'by_tenure_band',
        'by_gender',
        'payload',
        'calculated_at',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'headcount_total' => 'integer',
        'headcount_active' => 'integer',
        'headcount_inactive' => 'integer',
        'fte_total' => 'decimal:2',
        'full_time_count' => 'integer',
        'part_time_count' => 'integer',
        'contractor_count' => 'integer',
        'new_hires_count' => 'integer',
        'transfers_in_count' => 'integer',
        'transfers_out_count' => 'integer',
        'promotions_count' => 'integer',
        'terminations_count' => 'integer',
        'voluntary_exits_count' => 'integer',
        'involuntary_exits_count' => 'integer',
        'by_department' => 'array',
        'by_branch' => 'array',
        'by_job_grade' => 'array',
        'by_employment_type' => 'array',
        'by_tenure_band' => 'array',
        'by_gender' => 'array',
        'payload' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
