<?php

namespace App\Domains\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmTenantSetupHealth extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_tenant_setup_health';

    protected $fillable = [
        'tenant_id',
        'overall_score',
        'category_scores',
        'remediation_items',
        'last_assessed_at',
    ];

    protected $casts = [
        'overall_score' => 'decimal:2',
        'category_scores' => 'array',
        'remediation_items' => 'array',
        'last_assessed_at' => 'datetime',
    ];
}
