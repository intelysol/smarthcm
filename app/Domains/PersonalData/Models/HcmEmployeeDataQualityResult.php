<?php

namespace App\Domains\PersonalData\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeDataQualityResult extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_data_quality_results';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'completeness_score',
        'validity_score',
        'verification_score',
        'freshness_score',
        'overall_score',
        'issues_count',
        'calculated_at',
    ];

    protected $casts = [
        'completeness_score' => 'decimal:2',
        'validity_score' => 'decimal:2',
        'verification_score' => 'decimal:2',
        'freshness_score' => 'decimal:2',
        'overall_score' => 'decimal:2',
        'issues_count' => 'integer',
        'calculated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
