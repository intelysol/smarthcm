<?php

namespace App\Domains\Compensation\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CompensationCycle extends CompensationModel
{
    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'cycle_type',
        'currency',
        'starts_on',
        'ends_on',
        'effective_on',
        'status',
        'guidelines',
        'eligibility_rules',
        'is_confidential',
        'workflow_instance_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'effective_on' => 'date',
            'is_confidential' => 'boolean',
            'guidelines' => 'array',
            'eligibility_rules' => 'array',
        ];
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(CompensationBudget::class, 'compensation_cycle_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(CompensationRecommendation::class, 'compensation_cycle_id');
    }

    public function bonusAllocations(): HasMany
    {
        return $this->hasMany(BonusAllocation::class, 'compensation_cycle_id');
    }

    public function calibrationSessions(): HasMany
    {
        return $this->hasMany(CompensationCalibrationSession::class, 'compensation_cycle_id');
    }

    public function payrollExports(): HasMany
    {
        return $this->hasMany(CompensationPayrollExport::class, 'compensation_cycle_id');
    }

    public function meritMatrices(): HasMany
    {
        return $this->hasMany(CompensationMeritMatrix::class, 'compensation_cycle_id');
    }
}
