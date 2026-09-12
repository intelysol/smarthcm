<?php

namespace App\Domains\WorkforceIntelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommandCenterKpi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'hcm_command_center_kpis';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'kpi_code',
        'name',
        'category',
        'description',
        'unit',
        'target_direction',
        'target_min',
        'target_max',
        'aggregation_method',
        'source_system',
        'freshness_ttl_seconds',
        'security_classification',
        'lifecycle_status',
        'metadata',
    ];

    protected $casts = [
        'target_min' => 'decimal:4',
        'target_max' => 'decimal:4',
        'freshness_ttl_seconds' => 'integer',
        'metadata' => 'array',
    ];

    public function versions()
    {
        return $this->hasMany(CommandCenterKpiVersion::class, 'kpi_id');
    }

    public function values()
    {
        return $this->hasMany(CommandCenterKpiValue::class, 'kpi_id');
    }
}
