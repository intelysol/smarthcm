<?php

namespace App\Domains\Attendance\Models;

use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RosterPeriod extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'roster_periods';

    protected $attributes = [
        'version' => 1,
        'is_locked' => false,
        'validation_status' => 'unvalidated',
    ];

    protected $fillable = [
        'tenant_id',
        'company_id',
        'department_id',
        'name',
        'start_date',
        'end_date',
        'status',
        'version',
        'is_locked',
        'locked_at',
        'locked_by',
        'validation_status',
        'validation_summary',
        'schedule_quality_score',
        'estimated_labor_cost',
        'published_at',
        'published_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'published_at' => 'datetime',
        'locked_at' => 'datetime',
        'is_locked' => 'boolean',
        'version' => 'integer',
        'validation_summary' => 'array',
        'schedule_quality_score' => 'decimal:2',
        'estimated_labor_cost' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RosterAssignment::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function coverageRequirements(): HasMany
    {
        return $this->hasMany(HcmScheduleCoverageRequirement::class, 'roster_period_id');
    }

    public function openShifts(): HasMany
    {
        return $this->hasMany(HcmOpenShift::class, 'roster_period_id');
    }

    public function acknowledgements(): HasMany
    {
        return $this->hasMany(HcmScheduleAcknowledgement::class, 'roster_period_id');
    }

    public function optimizationRuns(): HasMany
    {
        return $this->hasMany(HcmScheduleOptimizationRun::class, 'roster_period_id');
    }
}
