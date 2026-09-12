<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollCalendar extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'payroll_calendars';

    protected $fillable = [
        'tenant_id',
        'payroll_legal_entity_id',
        'code',
        'name',
        'frequency',
        'period_start_day',
        'cutoff_day_offset',
        'pay_day_offset',
        'description',
        'is_default',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'period_start_day' => 'integer',
        'cutoff_day_offset' => 'integer',
        'pay_day_offset' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(PayrollLegalEntity::class, 'payroll_legal_entity_id');
    }

    public function periods(): HasMany
    {
        return $this->hasMany(PayrollPeriod::class);
    }
}
