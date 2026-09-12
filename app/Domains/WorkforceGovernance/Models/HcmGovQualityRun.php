<?php

namespace App\Domains\WorkforceGovernance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmGovQualityRun extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_gov_quality_runs';

    protected $fillable = [
        'tenant_id',
        'run_code',
        'domain',
        'trigger_type',
        'started_at',
        'completed_at',
        'total_rules_evaluated',
        'total_records_scanned',
        'total_issues_found',
        'critical_issues_found',
        'overall_score',
        'status',
        'dimension_scores',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_rules_evaluated' => 'integer',
        'total_records_scanned' => 'integer',
        'total_issues_found' => 'integer',
        'critical_issues_found' => 'integer',
        'overall_score' => 'decimal:2',
        'dimension_scores' => 'array',
    ];

    public function issues()
    {
        return $this->hasMany(HcmGovQualityIssue::class, 'run_id');
    }
}
