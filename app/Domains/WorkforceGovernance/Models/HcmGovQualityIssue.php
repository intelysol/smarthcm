<?php

namespace App\Domains\WorkforceGovernance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmGovQualityIssue extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_gov_quality_issues';

    protected $fillable = [
        'tenant_id',
        'run_id',
        'rule_id',
        'asset_id',
        'issue_code',
        'severity',
        'target_entity',
        'target_record_id',
        'target_field',
        'current_value',
        'expected_condition',
        'suggested_correction',
        'status',
        'assigned_steward_id',
        'root_cause_category',
        'root_cause_explanation',
        'resolved_by_user_id',
        'resolved_at',
        'due_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    public function rule()
    {
        return $this->belongsTo(HcmGovQualityRule::class, 'rule_id');
    }

    public function asset()
    {
        return $this->belongsTo(HcmGovAsset::class, 'asset_id');
    }

    public function exceptions()
    {
        return $this->hasMany(HcmGovQualityException::class, 'issue_id');
    }
}
