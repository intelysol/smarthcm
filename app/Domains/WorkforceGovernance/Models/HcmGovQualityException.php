<?php

namespace App\Domains\WorkforceGovernance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HcmGovQualityException extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'hcm_gov_quality_exceptions';

    protected $fillable = [
        'tenant_id',
        'issue_id',
        'approved_by_user_id',
        'justification',
        'valid_until',
        'status',
    ];

    protected $casts = [
        'valid_until' => 'datetime',
    ];

    public function issue()
    {
        return $this->belongsTo(HcmGovQualityIssue::class, 'issue_id');
    }
}
