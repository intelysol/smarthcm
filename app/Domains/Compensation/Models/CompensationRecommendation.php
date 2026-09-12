<?php

namespace App\Domains\Compensation\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\JobGrade;
use App\Domains\Platform\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompensationRecommendation extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'compensation_cycle_id',
        'employee_id',
        'job_grade_id',
        'currency',
        'current_base_salary',
        'recommended_base_salary',
        'increase_amount',
        'increase_percentage',
        'recommendation_type',
        'compa_ratio_current',
        'compa_ratio_new',
        'range_penetration_current',
        'range_penetration_new',
        'performance_rating',
        'guideline_min_pct',
        'guideline_max_pct',
        'promotion_grade_id',
        'promotion_increase_amount',
        'market_adjustment_amount',
        'lump_sum_amount',
        'components',
        'justification',
        'status',
        'proposed_by',
        'approved_by',
        'approved_at',
        'effective_date',
    ];

    protected function casts(): array
    {
        return [
            'current_base_salary' => 'decimal:2',
            'recommended_base_salary' => 'decimal:2',
            'increase_amount' => 'decimal:2',
            'increase_percentage' => 'decimal:4',
            'compa_ratio_current' => 'decimal:4',
            'compa_ratio_new' => 'decimal:4',
            'range_penetration_current' => 'decimal:4',
            'range_penetration_new' => 'decimal:4',
            'guideline_min_pct' => 'decimal:2',
            'guideline_max_pct' => 'decimal:2',
            'promotion_increase_amount' => 'decimal:2',
            'market_adjustment_amount' => 'decimal:2',
            'lump_sum_amount' => 'decimal:2',
            'components' => 'array',
            'approved_at' => 'datetime',
            'effective_date' => 'date',
        ];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(CompensationCycle::class, 'compensation_cycle_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class, 'job_grade_id');
    }

    public function promotionGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class, 'promotion_grade_id');
    }

    public function proposedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function calibrationRecords(): HasMany
    {
        return $this->hasMany(CompensationCalibrationRecord::class, 'compensation_recommendation_id');
    }
}
