<?php

namespace App\Domains\WorkforceIntelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandCenterSnapshot extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_command_center_snapshots';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'snapshot_code',
        'snapshot_name',
        'period_type',
        'period_key',
        'as_of_date',
        'total_headcount',
        'total_fte',
        'total_workforce_cost',
        'average_cost_per_fte',
        'composite_health_score',
        'overall_productivity_score',
        'turnover_rate',
        'absence_rate',
        'critical_risk_count',
        'open_decision_count',
        'summary_metrics',
        'dimension_breakdowns',
        'generated_by_user_id',
        'generated_at',
    ];

    protected $casts = [
        'total_headcount' => 'integer',
        'total_fte' => 'decimal:2',
        'total_workforce_cost' => 'decimal:2',
        'average_cost_per_fte' => 'decimal:2',
        'composite_health_score' => 'decimal:2',
        'overall_productivity_score' => 'decimal:2',
        'turnover_rate' => 'decimal:2',
        'absence_rate' => 'decimal:2',
        'critical_risk_count' => 'integer',
        'open_decision_count' => 'integer',
        'summary_metrics' => 'array',
        'dimension_breakdowns' => 'array',
        'as_of_date' => 'date',
        'generated_at' => 'datetime',
    ];
}
