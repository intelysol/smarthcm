<?php

namespace App\Domains\WorkforceGovernance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HcmGovAsset extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'hcm_gov_assets';

    protected $fillable = [
        'tenant_id',
        'company_id',
        'asset_code',
        'name',
        'domain',
        'business_definition',
        'technical_definition',
        'system_of_record',
        'source_table',
        'business_owner',
        'technical_owner',
        'data_steward',
        'security_classification',
        'freshness_status',
        'expected_refresh_seconds',
        'last_refreshed_at',
        'current_quality_score',
        'status',
        'metadata',
    ];

    protected $casts = [
        'last_refreshed_at' => 'datetime',
        'current_quality_score' => 'decimal:2',
        'expected_refresh_seconds' => 'integer',
        'metadata' => 'array',
    ];

    public function rules()
    {
        return $this->hasMany(HcmGovQualityRule::class, 'asset_id');
    }

    public function issues()
    {
        return $this->hasMany(HcmGovQualityIssue::class, 'asset_id');
    }
}
