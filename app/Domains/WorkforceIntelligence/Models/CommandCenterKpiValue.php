<?php

namespace App\Domains\WorkforceIntelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandCenterKpiValue extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_command_center_kpi_values';

    protected $fillable = [
        'tenant_id',
        'kpi_id',
        'department_id',
        'period_type',
        'period_key',
        'period_start',
        'period_end',
        'calculated_value',
        'target_value',
        'benchmark_value',
        'variance_value',
        'variance_percentage',
        'status_band',
        'calculated_at',
        'calculation_context',
    ];

    protected $casts = [
        'calculated_value' => 'decimal:4',
        'target_value' => 'decimal:4',
        'benchmark_value' => 'decimal:4',
        'variance_value' => 'decimal:4',
        'variance_percentage' => 'decimal:4',
        'period_start' => 'date',
        'period_end' => 'date',
        'calculated_at' => 'datetime',
        'calculation_context' => 'array',
    ];

    public function kpi()
    {
        return $this->belongsTo(CommandCenterKpi::class, 'kpi_id');
    }
}
