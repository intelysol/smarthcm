<?php

namespace App\Domains\WorkforceIntelligence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandCenterKpiVersion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_command_center_kpi_versions';

    protected $fillable = [
        'tenant_id',
        'kpi_id',
        'version_number',
        'formula_expression',
        'formula_variables',
        'dimension_bindings',
        'effective_from_period',
        'effective_to_period',
        'approved_by_user_id',
        'approved_at',
        'change_reason',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'formula_variables' => 'array',
        'dimension_bindings' => 'array',
        'approved_at' => 'datetime',
    ];

    public function kpi()
    {
        return $this->belongsTo(CommandCenterKpi::class, 'kpi_id');
    }
}
