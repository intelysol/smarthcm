<?php

namespace App\Domains\WorkforceGovernance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmGovKpiRegistry extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_gov_kpi_registries';

    protected $fillable = [
        'tenant_id',
        'kpi_code',
        'name',
        'business_definition',
        'technical_formula',
        'business_owner',
        'technical_owner',
        'source_module',
        'unit',
        'frequency',
        'version',
        'lifecycle_status',
        'certification_status',
        'certified_by_user_id',
        'certified_at',
        'security_classification',
    ];

    protected $casts = [
        'version' => 'integer',
        'certified_at' => 'datetime',
    ];
}
