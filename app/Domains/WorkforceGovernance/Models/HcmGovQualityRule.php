<?php

namespace App\Domains\WorkforceGovernance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmGovQualityRule extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_gov_quality_rules';

    protected $fillable = [
        'tenant_id',
        'asset_id',
        'rule_code',
        'name',
        'dimension',
        'severity',
        'description',
        'target_entity',
        'target_field',
        'condition_expression',
        'expected_condition_text',
        'suggested_remediation',
        'sla_hours',
        'is_active',
        'version',
    ];

    protected $casts = [
        'sla_hours' => 'integer',
        'is_active' => 'boolean',
        'version' => 'integer',
    ];

    public function asset()
    {
        return $this->belongsTo(HcmGovAsset::class, 'asset_id');
    }

    public function issues()
    {
        return $this->hasMany(HcmGovQualityIssue::class, 'rule_id');
    }
}
