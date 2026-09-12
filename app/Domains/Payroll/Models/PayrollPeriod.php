<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Payroll\Enums\PeriodStatus;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollPeriod extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'payroll_periods';

    protected $fillable = [
        'tenant_id',
        'payroll_calendar_id',
        'payroll_legal_entity_id',
        'payroll_group_id',
        'period_name',
        'start_date',
        'end_date',
        'cutoff_date',
        'payment_date',
        'currency',
        'status',
        'lock_date',
        'locked_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'cutoff_date' => 'date',
        'payment_date' => 'date',
        'lock_date' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(PayrollCalendar::class, 'payroll_calendar_id');
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(PayrollLegalEntity::class, 'payroll_legal_entity_id');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(PayrollRun::class);
    }

    public function inputs(): HasMany
    {
        return $this->hasMany(PayrollInput::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(PayrollAdjustment::class);
    }

    public function lockLogs(): HasMany
    {
        return $this->hasMany(PayrollPeriodLock::class);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, [PeriodStatus::LOCKED->value, PeriodStatus::PAID->value, PeriodStatus::CLOSED->value], true)
            || $this->lock_date !== null;
    }
}
